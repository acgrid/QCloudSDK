<?php

namespace QCloudSDKTests\TIM;

use GuzzleHttp\Psr7\Uri;
use QCloudSDK\TIM\Status;
use QCloudSDKTests\TestCase;

class StatusTest extends TestCase
{
    /**
     * @var Status
     */
    protected $status;

    protected function setUp(): void
    {
        parent::setUp();
        $this->status = new Status(SMSTest::EXAMPLE_CONFIG, $this->http, $this->logger);
    }

    public function testSingleStatus()
    {
        $time = time();
        $this->status->queryDelivery()->pullSingleStatus('86', '13712345678', $date = date('Y-m-d H:i:s', $time), $date, 5);
        $this->assertMyRequestUri(function (Uri $uri){
            $this->assertStringEndsWith('pullstatus4mobile', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) use ($time) {
            $this->assertSame(Status::TYPE_DELIVERY, $json['type']);
            $this->assertSame(5, $json['max']);
            $this->assertSame($time, $json['begin_time']);
            $this->assertSame($time, $json['end_time']);
            $this->assertSame('86', $json['nationcode']);
            $this->assertSame('13712345678', $json['mobile']);
        });
    }

    public function testMultiStatus()
    {
        $this->status->queryReply()->pullMultiStatus(20);
        $this->assertMyRequestUri(function (Uri $uri){
            $this->assertStringEndsWith('pullstatus', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) {
            $this->assertSame(Status::TYPE_REPLY, $json['type']);
            $this->assertSame(20, $json['max']);
        });
    }


    public function testSendStatus()
    {
        $time = time();
        $this->status->pullSendStatus($time, $time);
        $this->assertMyRequestUri(function (Uri $uri){
            $this->assertStringEndsWith('pullsendstatus', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) use ($time) {
            $this->assertSame(intval(date('YmdH', $time)), $json['begin_date']);
            $this->assertSame(intval(date('YmdH', $time)), $json['end_date']);
        });
    }

    public function testCallbackStatus()
    {
        $time = time();
        $this->status->pullCallbackStatus($time, $time);
        $this->assertMyRequestUri(function (Uri $uri){
            $this->assertStringEndsWith('pullcallbackstatus', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) use ($time) {
            $this->assertSame(intval(date('YmdH', $time)), $json['begin_date']);
            $this->assertSame(intval(date('YmdH', $time)), $json['end_date']);
        });
    }

}
