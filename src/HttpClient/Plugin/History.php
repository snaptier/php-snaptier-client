<?php

namespace Snaptier\API\HttpClient\Plugin;

use Http\Client\Common\Plugin\Journal;
use Http\Client\Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A plugin to remember the last response.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Graham Campbell <graham@alt-three.com>
 */
class History implements Journal
{
    /**
     * The last response.
     *
     * @var \Psr\Http\Message\ResponseInterface|null
     */
    private $lastResponse;

    /**
     * Get the last response.
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Record a successful call.
     *
     * @param \Psr\Http\Message\RequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return void
     */
    public function addSuccess(RequestInterface $request, ResponseInterface $response)
    {
        $this->lastResponse = $response;
    }

    /**
     * Record a failed call.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Http\Client\Exception             $exception
     *
     * @return void
     */
    public function addFailure(RequestInterface $request, Exception $exception)
    {
        // do nothing
    }
}
