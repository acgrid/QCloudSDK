<?php


namespace QCloudSDK\Core;


use QCloudSDK\Core\Exceptions\ClientException;
use QCloudSDK\Facade\Config;
use QCloudSDK\Utils\Collection;
use QCloudSDK\Utils\Log;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractAPI
{
    const CONFIG_SECTION = 'api';

    const RESPONSE_CODE = 'code';
    const RESPONSE_MESSAGE = 'message';

    const SUCCESS_CODE = 0;

    /**
     * @var Config
     */
    protected $config;
    /**
     * @var Http
     */
    protected $http;
    /**
     * @var int
     */
    protected $maxRetries;
    /**
     * @var array
     */
    protected $retryCodes = [];
    /**
     * @var bool
     */
    protected $signNeedMethod = true;
    /**
     * @var bool
     */
    protected $signNeedEndpoint = true;

    public function __construct(Config $config, Http $http = null)
    {
        $this->config = $config;
        $this->http = $http ?? new Http();
        $this->retryCodes = array_flip($this->retryCodes);
        $this->maxRetries = $this->getLocalConfig(Config::COMMON_MAX_RETRIES, 2);
        $this->init();
    }

    protected function init()
    {
        // Override
    }

    protected function getLocalConfig($key, $default = null)
    {
        return $this->config->get(static::CONFIG_SECTION . ".$key", $this->config->get($key, $default));
    }

    /**
     * Return the http instance.
     *
     * @return Http
     */
    public function getHttp()
    {
        if (count($this->http->getMiddlewares()) === 0) {
            $this->registerHttpMiddlewares();
        }
        return $this->http;
    }

    /**
     * Set the http instance.
     *
     * @param Http $http
     *
     * @return $this
     */
    public function setHttp(Http $http)
    {
        $this->http = $http;

        return $this;
    }

    /**
     * Parse JSON from response and check error.
     *
     * @param string $method
     * @param array  $args
     *
     * @return Collection
     */
    public function parseJSON($method, ...$args)
    {
        $http = $this->getHttp();

        $contents = $http->parseJSON($http->$method(...$args));

        $this->checkAndThrow($contents);

        return new Collection(is_scalar($contents) ? ['response' => $contents] : $contents);
    }

    /**
     * Sign request params and call parseJSON
     *
     * @param string $method
     * @param array $args
     * @return Collection
     */
    public function parseJSONSigned($method, ...$args)
    {
        if(isset($args[1])) $args[1] = $this->sign(strtoupper($method), $args[0], $args[1]);
        return $this->parseJSON($method, ...$args);
    }

    /**
     * @param string $method
     * @param array $args
     * @return ResponseInterface
     */
    public function requestSigned($method, ...$args)
    {
        if(isset($args[1])) $args[1] = $this->sign(strtoupper($method), $args[0], $args[1]);
        return $this->http->$method(...$args);
    }

    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
        $this->http->addMiddleware($this->retryMiddleware());

        $this->http->addMiddleware($this->logMiddleware());
    }

    /**
     * Log the request.
     *
     * @return \Closure
     */
    protected function logMiddleware()
    {
        return Middleware::tap(function (RequestInterface $request, $options) {
            Log::debug("Request: {$request->getMethod()} {$request->getUri()} ".json_encode($options));
            Log::debug('Request headers:'.json_encode($request->getHeaders()));
        });
    }

    /**
     * Return retry middleware.
     *
     * @return \Closure
     */
    protected function retryMiddleware()
    {
        return Middleware::retry(function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null
        ) {
            // Limit the number of retries to n
            if (++$retries <= $this->maxRetries && isset($response) && $response->getBody()->getSize() < 1048576) {
                if(preg_match('/' . preg_quote(json_encode(static::RESPONSE_CODE), '/') . ':\s*(\d+)/', strval($response->getBody()), $match) && isset($this->retryCodes[$match[1]])){
                    Log::debug("Request to {$request->getUri()->getPath()} Result Code {$match[1]}, Retry count {$retries}.");
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Check the array data errors, and Throw exception when the contents contains error.
     *
     * @param mixed $contents
     *
     * @throws ClientException
     */
    protected function checkAndThrow($contents)
    {
        if (isset($contents[static::RESPONSE_CODE]) && static::SUCCESS_CODE !== $contents[static::RESPONSE_CODE]) {
            if (empty($contents[static::RESPONSE_MESSAGE])) {
                $contents[static::RESPONSE_MESSAGE] = 'Unknown';
            }

            throw new ClientException($contents[static::RESPONSE_MESSAGE], $contents[static::RESPONSE_CODE]);
        }
    }

    public function sign($method, $urlWithoutScheme, array $params)
    {
        if(!method_exists($this, 'doSign')) return $params;
        $signData = [];
        if($this->signNeedMethod) $signData []= $method;
        if($this->signNeedEndpoint) $signData []= $urlWithoutScheme;
        $signData []= $params;
        return $this->doSign(...$signData);
    }

    protected function createParam($key, $action)
    {
        return [$key => $action];
    }

    /**
     * @param string $key
     * @param Collection $data
     * @param string $failureMsg
     * @return Collection
     */
    protected function expectResult(string $key, Collection $data, $failureMsg = 'Remote API do not return expected data.')
    {
        if(null === ($expected = $data->get($key))) throw new \LogicException($failureMsg);
        return new Collection($expected);
    }

}