<?php

use Rubix\ML\Datasets\Labeled;
use Rubix\Tests\Helpers\MockClassifier;
use Rubix\ML\Metrics\Validation\MCC;
use Rubix\ML\Metrics\Validation\Validation;
use Rubix\ML\Metrics\Validation\Classification;
use PHPUnit\Framework\TestCase;

class MCCTest extends TestCase
{
    protected $metric;

    public function setUp()
    {
        $this->testing = new Labeled([[], [], [], [], []],
            ['lamb', 'lamb', 'wolf', 'wolf', 'wolf']);

        $this->estimator = new MockClassifier([
            'wolf', 'lamb', 'wolf', 'lamb', 'wolf'
        ]);

        $this->metric = new MCC();
    }

    public function test_build_mcc_test()
    {
        $this->assertInstanceOf(MCC::class, $this->metric);
        $this->assertInstanceOf(Classification::class, $this->metric);
        $this->assertInstanceOf(Validation::class, $this->metric);
    }

    public function test_get_range()
    {
        $this->assertEquals([-1, 1], $this->metric->range());
    }

    public function test_score_predictions()
    {
        $score = $this->metric->score($this->estimator, $this->testing);

        $this->assertEquals(0.16666666555555557, $score);
    }
}