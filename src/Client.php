<?php

namespace Snaptier\API;

use Snaptier\API\HttpClient\Builder;
use Snaptier\API\HttpClient\Plugin\Authentication;
use Snaptier\API\HttpClient\Plugin\ExceptionThrower;
use Snaptier\API\Exceptions\InvalidArgumentException;
use Snaptier\API\HttpClient\Plugin\History;
use Http\Client\Common\Plugin\AddHostPlugin;
use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use Http\Client\Common\Plugin\HistoryPlugin;
use Http\Client\Common\Plugin\RedirectPlugin;
use Http\Discovery\UriFactoryDiscovery;

/**
 * The Snaptier API client.
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 * @author Graham Campbell <graham@alt-three.com>
 * @author Miguel Piedrafita <soy@miguelpiedrafita.com>
 */
class Client
{
    /**
     * The oauth token authentication method.
     *
     * @var string
     */
    const AUTH_OAUTH_TOKEN = 'oauth_token';

    /**
     * The http password authentication method.
     *
     * @var string
     */
    const AUTH_HTTP_PASSWORD = 'http_password';

    /**
     * The bitbucket http client builder.
     *
     * @var \Snaptier\API\HttpClient\Builder
     */
    private $httpClientBuilder;

    /**
     * The response history plugin.
     *
     * @var \Snaptier\API\HttpClient\Plugin\History
     */
    private $responseHistory;

    /**
     * Create a new Bitbucket API client instance.
     *
     * @param \Snaptier\API\HttpClient\Builder|null $httpClientBuilder
     *
     * @return void
     */
    public function __construct(Builder $httpClientBuilder = null)
    {
        $this->httpClientBuilder = $builder = $httpClientBuilder ?: new Builder();
        $this->responseHistory = new History();

        $builder->addPlugin(new ExceptionThrower());
        $builder->addPlugin(new HistoryPlugin($this->responseHistory));
        $builder->addPlugin(new RedirectPlugin());

        $builder->addPlugin(new HeaderDefaultsPlugin([
            'Accept'     => 'application/json',
            'User-Agent' => 'snaptier-php-client',
        ]));

        $this->setUrl('https://api.snaptier.co');
    }

    /**
     * @param string $name
     *
     * @throws InvalidArgumentException
     *
     * @return AbstractApi
     */
    public function api($name)
    {
        switch ($name) {
            case 'user':
            case 'users':
                $api = new Api\Users($this->getHttpClient());
                break;
            default:
                throw new InvalidArgumentException(sprintf('Undefined api instance called: "%s"', $name));
        }

        return $api;
    }

    /**
     * Authenticate a user for all next requests.
     *
     * @param string      $method
     * @param string      $token
     * @param string|null $password
     *
     * @return void
     */
    public function authenticate(string $method, string $token, string $password = null)
    {
        $this->getHttpClientBuilder()->removePlugin(Authentication::class);
        $this->getHttpClientBuilder()->addPlugin(new Authentication($method, $token, $password));
    }

    /**
     * Set the base URL.
     *
     * @param string $url
     *
     * @return void
     */
    public function setUrl(string $url)
    {
        $this->httpClientBuilder->removePlugin(AddHostPlugin::class);
        $this->httpClientBuilder->addPlugin(new AddHostPlugin(UriFactoryDiscovery::find()->createUri($url)));
    }

    /**
     * Get the last response.
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function getLastResponse()
    {
        return $this->responseHistory->getLastResponse();
    }

    /**
     * Get the http client.
     *
     * @return \Http\Client\Common\HttpMethodsClient
     */
    public function getHttpClient()
    {
        return $this->getHttpClientBuilder()->getHttpClient();
    }

    /**
     * Get the http client builder.
     *
     * @return \Snaptier\API\HttpClient\Builder
     */
    protected function getHttpClientBuilder()
    {
        return $this->httpClientBuilder;
    }

    /**
     * @param string $name
     *
     * @throws BadMethodCallException
     *
     * @return AbstractApi
     */
    public function __call($name, $args)
    {
        try {
            return $this->api($name);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException(sprintf('Undefined method called: "%s"', $name), 500, $e);
        }
    }
}
