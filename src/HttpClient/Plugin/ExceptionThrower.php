<?php

namespace Snaptier\API\HttpClient\Plugin;

use Snaptier\API\Exception\ApiLimitExceedException;
use Snaptier\API\Exception\BadRequestException;
use Snaptier\API\Exception\ClientErrorException;
use Snaptier\API\Exception\DecodingFailedException;
use Snaptier\API\Exception\ServerErrorException;
use Snaptier\API\Exception\ValidationFailedException;
use Snaptier\API\HttpClient\Message\ResponseMediator;
use Http\Client\Common\Plugin;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A plugin to throw bitbucket exceptions.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Fabien Bourigault <bourigaultfabien@gmail.com>
 * @author Graham Campbell <graham@alt-three.com>
 */
class ExceptionThrower implements Plugin
{
    /**
     * Handle the request and return the response coming from the next callable.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param callable                           $next
     * @param callable                           $first
     *
     * @return \Http\Promise\Promise
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        return $next($request)->then(function (ResponseInterface $response) {
            $status = $response->getStatusCode();

            if ($status >= 400 && $status < 600) {
                self::handleError($status, self::getMessage($response) ?: $response->getReasonPhrase());
            }

            return $response;
        });
    }

    /**
     * Handle an error response.
     *
     * @param int         $status
     * @param string|null $message
     *
     * @throws \Snaptier\API\Exception\RuntimeException
     *
     * @return void
     */
    private static function handleError(int $status, string $message = null)
    {
        if ($status === 400) {
            throw new BadRequestException($message, $status);
        }

        if ($status === 422) {
            throw new ValidationFailedException($message, $status);
        }

        if ($status === 429) {
            throw new ApiLimitExceededException($message, $status);
        }

        if ($status < 500) {
            throw new ClientErrorException($message, $status);
        }

        throw new ServerErrorException($message, $status);
    }

    /**
     * Get the error message from the response if present.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return string|null
     */
    private static function getMessage(ResponseInterface $response)
    {
        try {
            return ResponseMediator::getContent($response)['error'] ?? null;
        } catch (DecodingFailedException $e) {
            // return nothing
        }
    }
}
