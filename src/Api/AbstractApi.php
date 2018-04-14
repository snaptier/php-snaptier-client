<?php

namespace Snaptier\API\Api;

use Snaptier\API\Exception\InvalidArgumentException;
use Snaptier\API\HttpClient\Message\ResponseMediator;
use Http\Client\Common\HttpMethodsClient;

/**
 * The abstract snaptier api class.
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 * @author Graham Campbell <graham@alt-three.com>
 * @author Miguel Piedrafita <soy@miguelpiedrafita.com>
 */
abstract class AbstractApi
{
    /**
     * The http methods client.
     *
     * @var \Http\Client\Common\HttpMethodsClient
     */
    private $client;

    /**
     * Create a new api instance.
     *
     * @param \Http\Client\Common\HttpMethodsClient $client
     *
     * @return void
     */
    public function __construct(HttpMethodsClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get the http methods client.
     *
     * @return \Http\Client\Common\HttpMethodsClient
     */
    protected function getHttpClient()
    {
        return $this->client;
    }

    /**
     * Send a GET request with query params.
     *
     * @param string $path
     * @param array  $params
     * @param array  $headers
     *
     * @throws \Http\Client\Exception
     *
     * @return array
     */
    protected function get(string $path, array $params = [], array $headers = [])
    {
        $response = $this->pureGet($path, $params, $headers);

        return ResponseMediator::getContent($response);
    }

    /**
     * Send a GET request with query params.
     *
     * @param string $path
     * @param array  $params
     * @param array  $headers
     *
     * @throws \Http\Client\Exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function pureGet(string $path, array $params = [], array $headers = [])
    {
        if ($params) {
            $path .= '?'.http_build_query($params);
        }

        return $this->client->get($path, $headers);
    }

    /**
     * Send a POST request with JSON-encoded params.
     *
     * @param string $path
     * @param array  $params
     * @param array  $headers
     *
     * @throws \Http\Client\Exception
     *
     * @return array
     */
    protected function post(string $path, array $params = [], array $headers = [])
    {
        $body = self::createJsonBody($params);

        if ($body) {
            $headers = self::addJsonContentType($headers);
        }

        return $this->postRaw($path, $body, $headers);
    }

    /**
     * Send a POST request with raw data.
     *
     * @param string                                        $path
     * @param string|\Psr\Http\Message\StreamInterface|null $body
     * @param array                                         $headers
     *
     * @throws \Http\Client\Exception
     *
     * @return array
     */
    protected function postRaw(string $path, $body = null, array $headers = [])
    {
        $response = $this->client->post($path, $headers, $body);

        return ResponseMediator::getContent($response);
    }

    /**
     * Send a PUT request with JSON-encoded params.
     *
     * @param string $path
     * @param array  $params
     * @param array  $headers
     *
     * @throws \Http\Client\Exception
     *
     * @return array
     */
    protected function put(string $path, array $params = [], array $headers = [])
    {
        $body = self::createJsonBody($params);

        if ($body) {
            $headers = self::addJsonContentType($headers);
        }

        return $this->putRaw($path, $body, $headers);
    }

    /**
     * Send a PUT request with raw data.
     *
     * @param string                                        $path
     * @param string|\Psr\Http\Message\StreamInterface|null $body
     * @param array                                         $headers
     *
     * @throws \Http\Client\Exception
     *
     * @return array
     */
    protected function putRaw(string $path, $body = null, array $headers = [])
    {
        $response = $this->client->put($path, $headers, $body);

        return ResponseMediator::getContent($response);
    }

    /**
     * Send a DELETE request with JSON-encoded params.
     *
     * @param string $path
     * @param array  $params
     * @param array  $headers
     *
     * @throws \Http\Client\Exception
     *
     * @return array
     */
    protected function delete(string $path, array $params = [], array $headers = [])
    {
        $body = self::createJsonBody($params);

        if ($body) {
            $headers = self::addJsonContentType($headers);
        }

        return $this->deleteRaw($path, $body, $headers);
    }

    /**
     * Send a DELETE request with raw data.
     *
     * @param string                                        $path
     * @param string|\Psr\Http\Message\StreamInterface|null $body
     * @param array                                         $headers
     *
     * @throws \Http\Client\Exception
     *
     * @return array
     */
    protected function deleteRaw(string $path, $body = null, array $headers = [])
    {
        $response = $this->client->delete($path, $headers, $body);

        return ResponseMediator::getContent($response);
    }

    /**
     * Build a URL path from the given parts.
     *
     * @param string[] $parts
     *
     * @throws \Bitbucket\Exception\InvalidArgumentException
     *
     * @return string
     */
    protected static function buildPath(string ...$parts)
    {
        $parts = array_map(function (string $part) {
            if (!$part) {
                throw new InvalidArgumentException('Missing required parameter.');
            }

            return self::urlEncode($part);
        }, $parts);

        return implode('/', $parts);
    }

    /**
     * Create a JSON encoded version of an array of params.
     *
     * @param array $params
     *
     * @return string|null
     */
    private static function createJsonBody(array $params)
    {
        if ($params) {
            return json_encode($params);
        }
    }

    /**
     * Add the JSON content type to the headers if one is not already present.
     *
     * @param array $headers
     *
     * @return array
     */
    private static function addJsonContentType(array $headers)
    {
        return array_merge(['Content-Type' => 'application/json'], $headers);
    }

    /**
     * Encode the given string for a URL.
     *
     * @param string $str
     *
     * @return string
     */
    private static function urlEncode(string $str)
    {
        $str = rawurlencode($str);

        return str_replace('.', '%2E', $str);
    }
}
