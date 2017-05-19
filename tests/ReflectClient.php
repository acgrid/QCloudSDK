<?php


namespace QCloudSDKTests;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class ReflectClient extends Client
{
    private $phpHandler;

    public function __construct(callable $responder = null)
    {
        if (!isset($responder)) {
            $responder = function (RequestInterface $request, $options) {
                return new Response(200, ['X-Foo' => 'Mock'], json_encode([
                'method' => $request->getMethod(),
                'headers' => $request->getHeaders(),
                'uri' => strval($request->getUri()),
                'body' => strval($request->getBody()),
                'options' => $options,
            ]));
            };
        }
        $this->phpHandler = new PhpHandler($responder);
        parent::__construct(['handler' => HandlerStack::create($this->phpHandler)]);
    }

    public function assertRequest(\Closure $assertion)
    {
        $assertion($this->phpHandler->getLastRequest(), $this->phpHandler->getLastOptions());
    }
}
