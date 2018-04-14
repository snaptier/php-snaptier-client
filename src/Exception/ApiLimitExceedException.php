<?php

namespace Snaptier\Api\Exception;

/**
 * This is the api limit exceed exception class.
 *
 * @author Miguel Piedrafita <stloyd@gmail.com>
 */
class ApiLimitExceedException extends RuntimeException
{
    private $limit;
    private $reset;

    public function __construct($limit = 5000, $reset = 1800, $code = 0, $previous = null)
    {
        $this->limit = (int) $limit;
        $this->reset = (int) $reset;

        parent::__construct(sprintf('You have reached Snaptier hourly limit! Actual limit is: %d', $limit), $code, $previous);
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getResetTime()
    {
        return $this->reset;
    }
}
