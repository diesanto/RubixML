<?php

namespace Rubix\ML\Tests\Regressors;

use Rubix\ML\Learner;
use Rubix\ML\DataType;
use Rubix\ML\Estimator;
use Rubix\ML\Persistable;
use Rubix\ML\EstimatorType;
use Rubix\ML\Graph\Trees\CART;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Graph\Trees\DecisionTree;
use Rubix\ML\Regressors\RegressionTree;
use Rubix\ML\Datasets\Generators\HalfMoon;
use Rubix\ML\CrossValidation\Metrics\RSquared;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use RuntimeException;

/**
 * @group Regressors
 * @covers \Rubix\ML\Regressors\RegressionTree
 */
class RegressionTreeTest extends TestCase
{
    /**
     * The number of samples in the training set.
     *
     * @var int
     */
    protected const TRAIN_SIZE = 300;

    /**
     * The number of samples in the validation set.
     *
     * @var int
     */
    protected const TEST_SIZE = 20;
    
    /**
     * The minimum validation score required to pass the test.
     *
     * @var float
     */
    protected const MIN_SCORE = 0.9;

    /**
     * Constant used to see the random number generator.
     *
     * @var int
     */
    protected const RANDOM_SEED = 0;

    /**
     * @var \Rubix\ML\Datasets\Generators\HalfMoon
     */
    protected $generator;

    /**
     * @var \Rubix\ML\Regressors\RegressionTree
     */
    protected $estimator;

    /**
     * @var \Rubix\ML\CrossValidation\Metrics\RSquared
     */
    protected $metric;

    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new HalfMoon(4.0, -7.0, 1.0, 90, 0.02);

        $this->estimator = new RegressionTree(10, 2, 3, 1e-7);

        $this->metric = new RSquared();

        srand(self::RANDOM_SEED);
    }

    protected function assertPreConditions() : void
    {
        $this->assertFalse($this->estimator->trained());
    }
    
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(RegressionTree::class, $this->estimator);
        $this->assertInstanceOf(CART::class, $this->estimator);
        $this->assertInstanceOf(DecisionTree::class, $this->estimator);
        $this->assertInstanceOf(Learner::class, $this->estimator);
        $this->assertInstanceOf(Persistable::class, $this->estimator);
        $this->assertInstanceOf(Estimator::class, $this->estimator);
    }

    /**
     * @test
     */
    public function badMaxDepth() : void
    {
        $this->expectException(InvalidArgumentException::class);

        new RegressionTree(0);
    }

    /**
     * @test
     */
    public function type() : void
    {
        $this->assertEquals(EstimatorType::regressor(), $this->estimator->type());
    }

    /**
     * @test
     */
    public function compatibility() : void
    {
        $expected = [
            DataType::categorical(),
            DataType::continuous(),
        ];

        $this->assertEquals($expected, $this->estimator->compatibility());
    }

    /**
     * @test
     */
    public function params() : void
    {
        $expected = [
            'max_depth' => 10,
            'max_leaf_size' => 2,
            'max_features' => 3,
            'min_purity_increase' => 1.0E-7,
        ];

        $this->assertEquals($expected, $this->estimator->params());
    }
    
    /**
     * @test
     */
    public function trainPredictFeatureImportancesRules() : void
    {
        $training = $this->generator->generate(self::TRAIN_SIZE);
        $testing = $this->generator->generate(self::TEST_SIZE);

        $this->estimator->train($training);

        $this->assertTrue($this->estimator->trained());

        $predictions = $this->estimator->predict($testing);

        $score = $this->metric->score($predictions, $testing->labels());

        $this->assertGreaterThanOrEqual(self::MIN_SCORE, $score);

        $importances = $this->estimator->featureImportances();

        $this->assertCount(2, $importances);
        $this->assertEquals(1.0, array_sum($importances));

        $rules = $this->estimator->rules();

        $this->assertIsString($rules);
    }
    
    /**
     * @test
     */
    public function trainUnlabeled() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->estimator->train(Unlabeled::quick());
    }
    
    /**
     * @test
     */
    public function predictUntrained() : void
    {
        $this->expectException(RuntimeException::class);

        $this->estimator->predict(Unlabeled::quick());
    }
}
