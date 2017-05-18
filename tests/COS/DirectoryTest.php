<?php

namespace QCloudSDKTests\COS;

use GuzzleHttp\Psr7\Uri;
use QCloudSDK\COS\Directory;
use QCloudSDKTests\TestCase;

class DirectoryTest extends TestCase
{
    /**
     * @var Directory
     */
    protected $api;

    protected function setUp()
    {
        parent::setUp();
        $this->api = new Directory($this->configForTest(), $this->http);
    }

    public function testCreate()
    {
        $this->api->create('foo', 'bar');
        $this->assertMyRequestMethod('POST');
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayHasKey('Authorization', $headers);
            $this->assertArrayHasKey('Host', $headers);
            $this->assertContains('a=200001&b=newbucket', $signature = base64_decode($headers['Authorization'][0]));
            $this->assertStringEndsWith('f=', $signature);
            $this->assertSame('gz.file.myqcloud.com', $headers['Host'][0]);
        });
        $this->assertMyRequestUri(function (Uri $uri) {
            $this->assertStringEndsWith('foo/', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) {
            $this->assertSame('create', $json['op']);
            $this->assertSame('bar', $json['biz_attr']);
        });
    }

    public function testList()
    {
        $this->api->ls('foo/bar', 200);
        $this->assertMyRequestMethod('GET');
        $this->assertMyRequestUri(function (Uri $uri) {
            $this->assertStringEndsWith('/foo/bar', $uri->getPath());
            parse_str($uri->getQuery(), $params);
            $this->assertSame('list', $params['op']);
            $this->assertSame('200', $params['num']);
            $this->assertArrayNotHasKey('context', $params);
        });

        $this->api->ls('foo/baz/', 200, '233');
        $this->assertMyRequestUri(function (Uri $uri) {
            $this->assertStringEndsWith('/foo/baz/', $uri->getPath());
            parse_str($uri->getQuery(), $params);
            $this->assertSame('list', $params['op']);
            $this->assertSame('200', $params['num']);
            $this->assertSame('233', $params['context']);
        });
    }

    public function testStat()
    {
        $this->api->stat('foo/');
        $this->assertMyRequestUri(function (Uri $uri) {
            $this->assertStringEndsWith('/foo/', $uri->getPath());
            parse_str($uri->getQuery(), $params);
            $this->assertSame('stat', $params['op']);
        });
    }

    public function testDelete()
    {
        $this->api->delete('poi');
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayHasKey('Authorization', $headers);
            $this->assertStringEndsWith('&f=/200001/newbucket/poi/', base64_decode($headers['Authorization'][0]));
        });
        $this->assertMyRequestUri(function (Uri $uri) {
            $this->assertStringEndsWith('/poi/', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) {
            $this->assertSame('delete', $json['op']);
        });
    }

}
