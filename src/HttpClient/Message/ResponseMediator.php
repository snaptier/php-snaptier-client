<?php

namespace Snaptier\API\HttpClient\Message;

use Snaptier\API\Exception\ApiLimitExceedException;
use Snaptier\API\Exception\DecodingFailedException;
use Psr\Http\Message\ResponseInterface;

/**
 * This is the response mediator class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 * @author Miguel Piedrafita <soy@miguelpiedrafita.com>
 */
class ResponseMediator
{
    /**
     * Get the decoded response content.
     *
     * If the there is no response body, we will always return the empty array.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @throws \Snaptier\API\Exception\DecodingFailedException
     *
     * @return array
     */
    public static function getContent(ResponseInterface $response)
    {
        if ($response->getStatusCode() === 204) {
            return [];
        }

        $body = (string) $response->getBody();

        if (!$body) {
            return [];
        }

        if (strpos($response->getHeaderLine('Content-Type'), 'application/json') !== 0) {
            throw new DecodingFailedException('The content type header was not application/json.');
        }

        return self::jsonDecode($body);
    }
    
    /**
     * Decode the given JSON string to an array.
     *
     * @param string $body
     *
     * @throws \Snaptier\API\Exception\DecodingFailedException
     *
     * @return array
     */
    private static function jsonDecode(string $body)
    {
        $content = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $msg = json_last_error_msg();

            throw new DecodingFailedException(
                'Failed to decode the json response body.'.($msg ? " {$msg}." : '')
            );
        }

        if (!is_array($content)) {
            throw new DecodingFailedException(
                'Failed to decode the json response body. Expected to decode to an array.'
            );
        }

        return $content;
    }

     /**
     * @param ResponseInterface $response
     *
     * @throws \Snaptier\API\Exception\ApiLimitExceedException
     *
     * @return null|string
     */
    public static function getApiLimit(ResponseInterface $response)
    {
        $remainingCalls = $response->getHeaderLine('X-RateLimit-Remaining');

        if (null !== $remainingCalls && 1 > $remainingCalls) {
            throw new ApiLimitExceedException($remainingCalls);
        }

        return $remainingCalls;
    }

}
