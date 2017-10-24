<?php


namespace QCloudSDK\Core;


use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use QCloudSDK\Core\Exceptions\HttpException;

abstract class AbstractCallback
{
    const BAD_REQUEST = '400';
    const FORBIDDEN = '403';
    const NOT_FOUND = '404';

    protected $handlers = [];
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    public function __construct()
    {
        $this->fallback();
    }

    /**
     * Register fallback event handlers
     */
    protected function fallback()
    {
        $this->on(self::BAD_REQUEST, [$this, 'defaultBadRequest']);
        $this->on(self::FORBIDDEN, [$this, 'defaultForbidden']);
        $this->on(self::NOT_FOUND, [$this, 'defaultNotFound']);
    }

    public function on(string $event, callable $callback)
    {
        $this->handlers[$event] = $callback;
        return $this;
    }

    protected function trigger(string $event, ... $args)
    {
        return isset($this->handlers[$event]) ? call_user_func_array($this->handlers[$event], $args) : null;
    }

    protected function defaultBadRequest()
    {
        throw new HttpException('Request body is not valid', self::BAD_REQUEST);
    }

    protected function defaultForbidden()
    {
        throw new HttpException('Request is not authentic', self::FORBIDDEN);
    }

    protected function defaultNotFound()
    {
        throw new HttpException('No handler defined for such request', self::NOT_FOUND);
    }

    protected function checkRequest()
    {
        return true;
    }

    protected function checkAuthentic()
    {
        return true;
    }

    protected function dispatch()
    {
        return null;
    }

    protected function makeRespondJSON($result = 0, $errmsg = 'OK')
    {
        return json_encode(compact('result', 'errmsg'));
    }

    public function respond(ServerRequestInterface $serverRequest)
    {
        $this->request = $serverRequest;
        try{
            if(!$this->checkRequest()) $this->trigger(self::BAD_REQUEST);
            if(!$this->checkAuthentic()) $this->trigger(self::FORBIDDEN);
            if(!$this->dispatch()) $this->trigger(self::NOT_FOUND);
            return new Response(200, [], $this->makeRespondJSON());
        }catch (HttpException $e){
            return new Response($e->getCode(), [], $this->makeRespondJSON($e->getCode(), $e->getMessage()));
        }catch (\Exception $e){
            return new Response(500, [], $this->makeRespondJSON($e->getCode(), $e->getMessage()));
        }
    }

}