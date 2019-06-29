<?php


namespace QCloudSDK\Core;


use Psr\Log\LoggerInterface;
use QCloudSDK\Core\Exceptions\ClientException;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Arr;
use Tightenco\Collect\Support\Collection;

abstract class AbstractAPI
{
    const CONFIG_DEBUG = 'Debug';

    const RESPONSE_CODE = 'code';
    const RESPONSE_MESSAGE = 'message';

    const SUCCESS_CODE = 0;

    /**
     * @var array
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

    use DebugTrait;

    public function __construct(array $config, Http $http, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->http = $http;
        $this->retryCodes = array_flip($this->retryCodes);
        $this->maxRetries = Arr::get($config, Http::CONFIG_MAX_RETRIES, 2);
        if($config[self::CONFIG_DEBUG] ?? false) $this->debug('Current config:', $this->dump($config));
        $this->init();
    }

    protected function init()
    {
        // Override
    }

    private function dump(array $config)
    {
        foreach($config as $key => $value){
            if(is_array($value)) $config[$key] = $this->dump($config[$key]);
            if(stripos($key, 'key') !== false || stripos($key, 'id') !== false) $config[$key] = '***'.substr($config[$key], -5);
        }
        return $config;
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
     * @throws
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
            $this->debug("Request: {$request->getMethod()} {$request->getUri()} ".json_encode($options));
            $this->debug('Request headers:'.json_encode($request->getHeaders()));
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
                    $this->debug("Request to {$request->getUri()->getPath()} Result Code {$match[1]}, Retry count {$retries}.");
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
        if(null === ($expected = Arr::get($data, $key))) throw new \LogicException($failureMsg);
        return new Collection($expected);
    }

}