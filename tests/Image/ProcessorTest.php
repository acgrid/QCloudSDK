<?php

namespace QCloudSDKTests\Image;

use GuzzleHttp\Psr7\Uri;
use QCloudSDK\Core\Exceptions\InvalidArgumentException;
use QCloudSDK\Image\ProcessingChain;
use QCloudSDK\Image\Processor;

class ProcessorTest extends ImageTestCase
{
    /**
     * @var Processor
     */
    protected $api;

    protected function setUp(): void
    {
        parent::setUp();
        $this->api = new Processor(static::EXAMPLE_CONFIG, $this->http, $this->logger);
    }

    public function testRequest()
    {
        $this->api->file('/abc/foo.jpg')->style('face')->exif();
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertSame('http', $uri->getScheme());
            $this->assertSame('tencentyun-1252821871.picgz.myqcloud.com', $uri->getHost());
            $this->assertSame('/abc/foo.jpg!face', $uri->getPath());
            $this->assertStringContainsString('exif', $uri->getQuery());
        });
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayHasKey('Authorization', $headers);
            parse_str(substr(base64_decode($headers['Authorization'][0]), 20), $params);
            $this->assertSame('/abc/foo.jpg', $params['f']);
            $this->assertEqualsWithDelta(60, $params['e'] - $params['t'], 1);
        });
        // should reset
        $this->api->public()->file('foo/bar.png')->ave();
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertSame('/foo/bar.png', $uri->getPath());
            $this->assertStringContainsString('imageAve', $uri->getQuery());
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
            $this->assertStringContainsString('imageInfo', $uri->getQuery());
            $this->assertStringContainsString('sign=', $uri->getQuery());
        });
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayHasKey('Authorization', $headers);
            parse_str(substr(base64_decode($headers['Authorization'][0]), 20), $params);
            $this->assertSame('/abc/xyz.jpg', $params['f']);
            $this->assertEqualsWithDelta(150, $params['e'] - $params['t'], 1);
        });
        // custom download
        $this->api->region('sh')->bucket('portrait')->direct()->file('foo.jpg')->separator('!')->style('face')->download();
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertSame('http', $uri->getScheme());
            $this->assertSame('portrait-1252821871.picsh.myqcloud.com', $uri->getHost());
            $this->assertSame('/foo.jpg!face', $uri->getPath());
            $this->assertStringContainsString('sign=', $uri->getQuery());
        });
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayHasKey('Authorization', $headers);
        });
        // get signed URL
        $this->assertStringContainsString('nep.jpg?sign=', $this->api->file('nep.jpg')->absoluteUrl(true));
        $this->assertStringContainsString('nep.jpg?custom/v/1&sign=', $this->api->file('nep.jpg')->chain(new class implements ProcessingChain{
            public function queryString()
            {
                return 'custom/v/1';
            }

        })->absoluteUrl(true));
        $this->api->reset();
        // public custom domain download
        $this->api->domain('https://portrait.example.com/')->public()->file('bar.jpg')->download();
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
