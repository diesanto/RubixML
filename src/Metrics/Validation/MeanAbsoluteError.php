<?php

namespace Rubix\ML\Metrics\Validation;

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Regressors\Regressor;

class MeanAbsoluteError implements Regression
{
    /**
     * Return a tuple of the min and max output value for this metric.
     *
     * @return array
     */
    public function range() : array
    {
        return [-INF, 0];
    }

    /**
     * Calculate the negative mean absolute error of the predictions.
     *
     * @param  \Rubix\ML\Regressors\Regressor  $estimator
     * @param  \Runix\Engine\Datasets\Labeled  $testing
     * @return float
     */
    public function score(Regressor $estimator, Labeled $testing) : float
    {
        $error = 0.0;

        foreach ($estimator->predict($testing) as $i => $prediction) {
            $error += abs($testing->label($i) - $prediction);
        }

        return -($error / ($testing->numRows() + self::EPSILON));
    }
}