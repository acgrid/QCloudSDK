<?php

namespace QCloudSDKTests\Image;

use GuzzleHttp\Psr7\Uri;
use QCloudSDK\Core\Exceptions\InvalidArgumentException;
use QCloudSDK\Image\Processor;
use QCloudSDKTests\TestCase;

class ProcessorTest extends TestCase
{
    /**
     * @var Processor
     */
    protected $api;

    protected function setUp()
    {
        parent::setUp();
        $this->api = new Processor($this->configForTest(), $this->http);
    }

    public function testRequest()
    {
        $this->api->file('/abc/foo.jpg')->style('face')->exif();
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertSame('http', $uri->getScheme());
            $this->assertSame('tencentyun-1252821871.picgz.myqcloud.com', $uri->getHost());
            $this->assertSame('/abc/foo.jpg!face', $uri->getPath());
            $this->assertSame('exif', $uri->getQuery());
        });
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayHasKey('Authorization', $headers);
            parse_str(substr(base64_decode($headers['Authorization'][0]), 20), $params);
            $this->assertSame('abc/foo.jpg', $params['f']);
            $this->assertEquals(60, $params['e'] - $params['t'], 'Default TTL in config file should be 60.', 1);
        });
        // should reset
        $this->api->public()->file('foo/bar.png')->ave();
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertSame('/foo/bar.png', $uri->getPath());
            $this->assertSame('imageAve', $uri->getQuery());
        });
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayNotHasKey('Authorization', $headers);
        });
        // missing filename
        try{
            $this->api->private(150)->download();
            $this->fail('Should complain empty filename');
        }catch (InvalidArgumentException $e){}
        // private state should hold
        $this->api->cdn()->file('abc/xyz.jpg')->separator('-')->style('thumbnail')->info();
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertSame('http', $uri->getScheme());
            $this->assertSame('tencentyun-1252821871.image.myqcloud.com', $uri->getHost());
            $this->assertSame('/abc/xyz.jpg-thumbnail', $uri->getPath());
            $this->assertSame('imageInfo', $uri->getQuery());
        });
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayHasKey('Authorization', $headers);
            parse_str(substr(base64_decode($headers['Authorization'][0]), 20), $params);
            $this->assertSame('abc/xyz.jpg', $params['f']);
            $this->assertEquals(150, $params['e'] - $params['t'], 'TTL should be overridden by 150', 1);
        });
        // custom download
        $this->api->region('sh')->bucket('portrait')->direct()->file('foo.jpg')->separator('!')->style('face')->download();
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertSame('http', $uri->getScheme());
            $this->assertSame('portrait-1252821871.picsh.myqcloud.com', $uri->getHost());
            $this->assertSame('/foo.jpg!face', $uri->getPath());
            $this->assertEmpty($uri->getQuery());
        });
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayHasKey('Authorization', $headers);
        });
        // get signed URL
        $this->assertContains('nep.jpg?sign=', $this->api->file('nep.jpg')->absoluteUrl(true));
        $this->assertContains('nep.jpg?exif&sign=', $this->api->file('nep.jpg')->query('exif')->absoluteUrl(true));
        $this->api->reset();
        // public custom domain download
        $this->api->domain('https://portrait.example.com')->public()->file('bar.jpg')->download();
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertSame('https', $uri->getScheme());
            $this->assertSame('portrait.example.com', $uri->getHost());
            $this->assertSame('/bar.jpg', $uri->getPath());
            $this->assertEmpty($uri->getQuery());
        });
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayNotHasKey('Authorization', $headers);
        });
    }

}
