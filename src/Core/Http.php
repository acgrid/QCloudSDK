<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Http.php.
 *
 * This file is part of the wechat-components.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace QCloudSDK\Core;

use QCloudSDK\Core\Exceptions\HttpException;
use QCloudSDK\Utils\Log;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Http.
 */
class Http
{
    /**
     * Used to identify handler defined by client code
     * Maybe useful in the future.
     */
    const USER_DEFINED_HANDLER = 'userDefined';

    /**
     * Http client.
     *
     * @var HttpClient
     */
    private $client;

    /**
     * The middlewares.
     *
     * @var array
     */
    protected $middlewares = [];
    /**
     * @var mixed
     */
    private $parsedJSON;

    /**
     * Guzzle client default settings.
     *
     * @var array
     */
    protected static $defaults = [
        'curl' => [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        ],
    ];

    /**
     * Set guzzle default settings.
     *
     * @param array $defaults
     */
    public static function setDefaultOptions($defaults = [])
    {
        self::$defaults = $defaults;
    }

    /**
     * Return current guzzle default settings.
     *
     * @return array
     */
    public static function getDefaultOptions()
    {
        return self::$defaults;
    }

    /**
     * GET request.
     *
     * @param string $url
     * @param array  $query
     *
     * @return ResponseInterface
     *
     * @throws HttpException
     */
    public function get($url, array $query = [])
    {
        return $this->request($url, 'GET', compact('query'));
    }

    /**
     * POST request.
     *
     * @param string       $url
     * @param array|string $data
     * @param array        $query
     *
     * @return ResponseInterface
     *
     * @throws HttpException
     */
    public function post($url, $data = [], array $query = [])
    {
        $options = [is_array($data) ? 'form_params' : 'body' => $data];
        if(count($query)) $options += compact('query');
        return $this->request($url, 'POST', $options);
    }

     /**
      * JSON request.
      *
      * @param string       $url
      * @param string|array $json
      * @param array $query
      * @param int          $encodeOption
      *
      * @return ResponseInterface
      *
      * @throws HttpException
      */
     public function json($url, $json = [], array $query = [], $encodeOption = JSON_UNESCAPED_UNICODE)
     {
         is_array($json) && $json = json_encode($json, $encodeOption);

         return $this->request($url, 'POST', ['query' => $query, 'body' => $json, 'headers' => ['content-type' => 'application/json']]);
     }

    /**
     * Upload file.
     *
     * @param string $url
     * @param array  $form
     * @param array  $files
     * @param array  $query
     * @return ResponseInterface
     *
     * @throws HttpException
     */
    public function upload($url, array $form = [], array $files = [], array $query = [])
    {
        $multipart = [];

        foreach ($files as $name => $path) {
            $multipart[] = [
                'name' => $name,
                'contents' => fopen($path, 'r'),
            ];
        }

        foreach ($form as $name => $contents) {
            $multipart[] = compact('name', 'contents');
        }

        return $this->request($url, 'POST', compact('query', 'multipart'));
    }

    /**
     * Set GuzzleHttp\Client.
     *
     * @param \GuzzleHttp\Client $client
     *
     * @return Http
     */
    public function setClient(HttpClient $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Return GuzzleHttp\Client instance.
     *
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        unset($this->parsedJSON); // Invalidate latest parsed JSON data

        if (!($this->client instanceof HttpClient)) {
            $this->client = new HttpClient();
        }

        return $this->client;
    }

    /**
     * Add a middleware.
     *
     * @param callable $middleware
     *
     * @return $this
     */
    public function addMiddleware(callable $middleware)
    {
        array_push($this->middlewares, $middleware);

        return $this;
    }

    /**
     * Return all middlewares.
     *
     * @return array
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * Make a request.
     *
     * @param string $url
     * @param string $method
     * @param array  $options
     *
     * @return ResponseInterface
     *
     * @throws HttpException
     */
    public function request($url, $method = 'GET', $options = [])
    {
        $method = strtoupper($method);

        if(substr($url, 0, 4) !== 'http') $url = "https://$url";

        $options = array_merge(self::$defaults, $options);

        Log::debug('Client Request:', compact('url', 'method', 'options'));

        $options['handler'] = $this->getHandler();

        $response = $this->getClient()->request($method, $url, $options);

        Log::debug('API response:', [
            'Status' => $response->getStatusCode(),
            'Reason' => $response->getReasonPhrase(),
            'Headers' => $response->getHeaders(),
            'Body' => strval($response->getBody()),
        ]);

        return $response;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface|string $body
     *
     * @return mixed
     *
     * @throws HttpException
     */
    public function parseJSON($body)
    {
        if(isset($this->parsedJSON)) return $this->parsedJSON;

        unset($this->parsedJSON);

        if ($body instanceof ResponseInterface) {
            $body = $body->getBody()->__toString();
        }

        Log::debug('API response raw:', compact('body'));

        if (empty($body)) throw new HttpException('Empty response but JSON expected.');

        $contents = json_decode($body, true);
        $json_result = json_last_error();

        Log::debug('API response decoded:', compact('contents'));

        if (JSON_ERROR_NONE !== $json_result) {
            throw new HttpException('Failed to parse JSON: '.json_last_error_msg());
        }

        return $this->parsedJSON = $contents;
    }

    /**
     * Build a handler.
     *
     * @return HandlerStack
     */
    protected function getHandler()
    {
        $stack = $this->getClient()->getConfig('handler');

        if(!isset($stack)) $stack = HandlerStack::create();

        foreach ($this->middlewares as $middleware) {
            $stack->push($middleware);
        }

        if (isset(static::$defaults['handler']) && is_callable(static::$defaults['handler'])) {
            $stack->push(static::$defaults['handler'], self::USER_DEFINED_HANDLER);
        }

        return $stack;
    }
}
