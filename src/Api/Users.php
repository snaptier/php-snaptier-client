<?php

namespace Snaptier\API\Api;

use Http\Client\Common\HttpMethodsClient;

/**
 * The users api class.
 *
 * @author Miguel Piedrafita <soy@miguelpiedrafita.com>
 */
class Users extends AbstractApi
{
    /**
     * Create a new users api instance.
     *
     * @param \Http\Client\Common\HttpMethodsClient $client
     */
    public function __construct(HttpMethodsClient $client)
    {
        parent::__construct($client);
    }

    /**
     * @throws \Http\Client\Exception
     *
     * @return array
     */
    public function me()
    {
        return $this->get('user');
    }
}
