<?php

namespace QCloudSDKTests\Core;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use QCloudSDKTests\Callback;

class CallbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \QCloudSDKTests\Callback
     */
    protected $handler;

    protected $sampleA = ['key' => Callback::SAMPLE_KEY, 'action' => 'A', 'foo' => 'bar'];
    protected $sampleC = ['key' => Callback::SAMPLE_KEY, 'action' => 'C', 'foo' => 'baz'];

    protected $touched = false;

    public function setUp()
    {
        $this->handler = new Callback();
        $this->handler->onA(function($data){
            $this->assertSame($data, $this->sampleA);
            $this->touched = true;
        });
    }

    protected function doCommonTest(ServerRequestInterface $serverRequest, $eventCode)
    {
        $response = json_decode(strval($this->handler->respond($serverRequest)->getBody()));
        $this->assertSame(intval($eventCode), $response->result);
        $badFlag = false;
        $this->handler->on($eventCode, function() use (&$badFlag){
            $badFlag = true;
        });
        $this->handler->respond($serverRequest);
        $this->assertTrue($badFlag);
    }

    public function testCommonHandlers()
    {
        $this->doCommonTest(new ServerRequest('POST', '/foo', [], 'not a json'), Callback::BAD_REQUEST);
        $this->doCommonTest(new ServerRequest('POST', '/foo', [], '{}'), Callback::FORBIDDEN);
        $this->doCommonTest(new ServerRequest('POST', '/foo', [], json_encode(['key' => Callback::SAMPLE_KEY])), Callback::NOT_FOUND);
    }

    public function testDispatch()
    {
        $this->handler->respond(new ServerRequest('POST', '/foo', [], json_encode($this->sampleA)));
        $this->assertSame('A', $this->handler->action);
        $this->assertTrue($this->touched);

        $response = json_decode(strval($this->handler->respond(new ServerRequest('POST', '/foo', [], json_encode($this->sampleC)))->getBody()));
        $this->assertSame('C', $this->handler->action);
        $this->assertSame(0, $response->result);
        $this->assertSame('OK', $response->errmsg);
    }


}
