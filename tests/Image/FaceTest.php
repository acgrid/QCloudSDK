<?php

namespace QCloudSDKTests\Image;

use function GuzzleHttp\Psr7\stream_for;
use GuzzleHttp\Psr7\Uri;
use QCloudSDK\Image\Face;

class FaceTest extends ImageTestCase
{
    /**
     * @var Face
     */
    protected $api;

    public function testDetectUrl()
    {
        $this->api->detect($url = 'http://mashi.ro/moe.png');
        $this->assertMyRequestMethod('POST');
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertSame('http://service.test.image.myqcloud.com/face/detect', $uri->__toString());
        });
        $this->assertMyRequestJson(function($json) use ($url){
            $this->assertSame($url, $json['url']);
            $this->assertSame(Face::DETECT_BIGGEST_FACE, $json['mode']);
            $this->assertArrayNotHasKey('image', $json);
        });
    }

    public function testDetectFile()
    {
        $this->api->detect(__FILE__, Face::DETECT_ALL_FACES);
        $this->assertMyRequestBody(function($body){
            $this->assertStringContainsString($this->makeFormData('mode', Face::DETECT_ALL_FACES), $body);
            $this->assertStringContainsString($this->makeFormData('image', file_get_contents(__FILE__), basename(__FILE__)), $body);
        });
    }

    public function testDetectStream()
    {
        $content = file_get_contents(__FILE__);
        $this->api->detect(stream_for($content));
        $this->assertMyRequestBody(function($body) use ($content) {
            $this->assertStringContainsString($this->makeFormData('image', $content), $body);
        });
    }

    public function testDetectString()
    {
        $this->api->detect($content = 'Pretend I am a PNG file.');
        $this->assertMyRequestBody(function($body) use ($content) {
            $this->assertStringContainsString($this->makeFormData('image', $content), $body);
        });
    }

    public function testShape()
    {
        $this->api->shape('http://mashi.ro/moe.png');
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertSame('http://service.test.image.myqcloud.com/face/shape', $uri->__toString());
        });
    }

    public function testVerify()
    {
        $this->api->verify($url = 'http://mashi.ro/moe.png', $person = 'Wow');
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertSame('http://service.test.image.myqcloud.com/face/verify', $uri->__toString());
        });
        $this->assertMyRequestJson(function($json) use ($url, $person){
            $this->assertSame($url, $json['url']);
            $this->assertSame($person, $json['person_id']);
        });
    }

    public function testCompare()
    {
        $this->api->compare($a = 'http://example.com/you.png', $b = 'http://example.com/me.png');
        $this->assertMyRequestMethod('POST');
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertSame('http://service.test.image.myqcloud.com/face/compare', $uri->__toString());
        });
        $this->assertMyRequestJson(function($body) use ($a, $b) {
            $this->assertSame($a, $body['urlA']);
            $this->assertSame($b, $body['urlB']);
        });
        $this->api->compare($a = 'My photo', $b = 'Your photo');
        $this->assertMyRequestMethod('POST');
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertSame('http://service.test.image.myqcloud.com/face/compare', $uri->__toString());
        });
        $this->assertMyRequestBody(function($body) use ($a, $b) {
            $this->assertStringContainsString($this->makeFormData('imageA', $a), $body);
            $this->assertStringContainsString($this->makeFormData('imageB', $b), $body);
        });
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->api = new Face(static::EXAMPLE_CONFIG, $this->http, $this->logger);
    }

}
