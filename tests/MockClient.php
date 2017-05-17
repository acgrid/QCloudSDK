<?php


namespace QCloudSDKTests;


use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class MockClient
{

    public static function make($body = null, $status = 200, array $headers = ['X-Foo' => 'Bar'], string $protocol = '1.1')
    {
        return new Client(['handler' => HandlerStack::create(new MockHandler([new Response($status, $headers, $body, $protocol)]))]);
    }

    public static function repeat(int $quantity, $body = null, $status = 200, array $headers = ['X-Foo' => 'Bar'], string $protocol = '1.1')
    {
        return new Client(['handler' => HandlerStack::create(new MockHandler(
            array_fill(0, $quantity, new Response($status, $headers, $body, $protocol))
        ))]);
    }

}