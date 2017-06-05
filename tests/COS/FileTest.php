<?php

namespace QCloudSDKTests\COS;

use GuzzleHttp\Psr7\Uri;
use QCloudSDK\COS\File;
use QCloudSDKTests\TestCase;

class FileTest extends TestCase
{
    /**
     * @var File
     */
    protected $api;

    protected function setUp()
    {
        parent::setUp();
        $this->api = new File($this->configForTest(), $this->http);
    }

    public function testUploadBulk()
    {
        $content = "<?php\nphpinfo();";
        $this->api->uploadString('/x//foo.php', $content, 'bar', true);
        $this->assertMyRequestMethod('POST');
        $this->assertMyRequestUri(function (Uri $uri) {
            $this->assertStringStartsWith('https://', $uri->__toString());
            $this->assertStringEndsWith('newbucket/x/foo.php', $uri->getPath());
        });
        $this->assertMyRequestBody(function ($body) use ($content) {
            $this->assertContains($this->makeFormData('op', 'upload'), $body);
            $this->assertContains($this->makeFormData('filecontent', $content), $body);
            $this->assertContains($this->makeFormData('sha1', sha1($content)), $body);
            $this->assertContains($this->makeFormData('biz_attr', 'bar'), $body);
            $this->assertContains($this->makeFormData('insertOnly', '1'), $body);
        });

        $this->api->uploadFile('bar.php', __FILE__);
        $this->assertMyRequestBody(function ($body) use ($content) {
            $this->assertContains($this->makeFormData('op', 'upload'), $body);
            $this->assertContains($this->makeFormData('filecontent', file_get_contents(__FILE__), basename(__FILE__)), $body);
            $this->assertContains($this->makeFormData('sha1', sha1_file(__FILE__)), $body);
            $this->assertNotContains("name=\"biz_attr\"", $body); // Escape myself
            $this->assertNotContains("name=\"insertOnly\"", $body);
        });
    }

    public function testUploadSlice()
    {
        $this->api->uploadSliceInit('foo.txt', 9543565441, File::SLICE_1MB);
        $this->assertMyRequestBody(function ($body) {
            $this->assertContains($this->makeFormData('op', 'upload_slice_init'), $body);
            $this->assertContains($this->makeFormData('filesize', 9543565441), $body);
            $this->assertContains($this->makeFormData('slice_size', File::SLICE_1MB), $body);
        });

        $this->api->uploadSliceData('foo.txt', 'foo', '641151102', 1048576);
        $this->assertMyRequestBody(function ($body) {
            $this->assertContains($this->makeFormData('op', 'upload_slice_data'), $body);
            $this->assertContains($this->makeFormData('filecontent', 'foo'), $body);
            $this->assertContains($this->makeFormData('session', '641151102'), $body);
            $this->assertContains($this->makeFormData('offset', 1048576), $body);
        });

        $this->api->uploadSliceFinish('foo.txt', '641151102', 9543565441);
        $this->assertMyRequestBody(function ($body) {
            $this->assertContains($this->makeFormData('op', 'upload_slice_finish'), $body);
            $this->assertContains($this->makeFormData('filesize', 9543565441), $body);
            $this->assertContains($this->makeFormData('session', '641151102'), $body);
        });

        $this->api->uploadSliceList('foo.txt');
        $this->assertMyRequestMethod('POST');
        $this->assertMyRequestBody(function ($body) {
            $this->assertContains($this->makeFormData('op', 'upload_slice_list'), $body);
        });
    }

    public function testAlter()
    {
        $this->api->move('a.txt', 'b.txt', false);
        $this->assertMyRequestMethod('POST');
        $this->assertMyRequestUri(function (Uri $uri) {
            $this->assertStringEndsWith('/a.txt', $uri->getPath());
        });
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayHasKey('Authorization', $headers);
            $this->assertStringEndsWith('&f=/200001/newbucket/a.txt', base64_decode($headers['Authorization'][0]));
        });
        $this->assertMyRequestBody(function ($body) {
            $this->assertContains($this->makeFormData('op', 'move'), $body);
            $this->assertContains($this->makeFormData('dest_fileid', 'b.txt'), $body);
            $this->assertContains($this->makeFormData('to_over_write', '0'), $body);
        });

        $this->api->copy('b.txt', 'a.txt', true);
        $this->assertMyRequestMethod('POST');
        $this->assertMyRequestUri(function (Uri $uri) {
            $this->assertStringEndsWith('/b.txt', $uri->getPath());
        });
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayHasKey('Authorization', $headers);
            $this->assertStringEndsWith('&f=/200001/newbucket/b.txt', base64_decode($headers['Authorization'][0]));
        });
        $this->assertMyRequestBody(function ($body) {
            $this->assertContains($this->makeFormData('op', 'copy'), $body);
            $this->assertContains($this->makeFormData('dest_fileid', 'a.txt'), $body);
            $this->assertContains($this->makeFormData('to_over_write', '1'), $body);
        });

        $this->api->stat('foo');
        $this->assertMyRequestMethod('GET');
        $this->assertMyRequestUri(function (Uri $uri) {
            $this->assertStringEndsWith('/foo', $uri->getPath());
            parse_str($uri->getQuery(), $params);
            $this->assertSame('stat', $params['op']);
        });

        $this->api->update('foo', [
            File::ATTR_AUTHORITY => File::AUTH_W_PRIVATE_R_PUBLIC,
            File::ATTR_HEADERS => $headers = ['Content-Type' => 'application/pdf'],
        ]);
        $this->assertMyRequestMethod('POST');
        $this->assertMyRequestJson(function ($json) use ($headers) {
            $this->assertSame('update', $json['op']);
            $this->assertSame(File::AUTH_W_PRIVATE_R_PUBLIC, $json[File::ATTR_AUTHORITY]);
            $this->assertSame($headers, $json[File::ATTR_HEADERS]);
        });

    }

    public function testDelete()
    {
        $this->api->delete('foo');
        $this->assertMyRequestMethod('POST');
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayHasKey('Authorization', $headers);
            $this->assertStringEndsWith('&f=/200001/newbucket/foo', base64_decode($headers['Authorization'][0]));
        });
        $this->assertMyRequestUri(function (Uri $uri) {
            $this->assertStringEndsWith('/foo', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) {
            $this->assertSame('delete', $json['op']);
        });
    }

    public function testDownload()
    {
        $this->api->downloadPublic('foo.html');
        $this->assertMyRequestMethod('GET');
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayNotHasKey('Authorization', $headers);
            $this->assertSame('newbucket-200001.gz.mycloud.com', $headers['Host'][0]);
        });
        $this->assertMyRequestUri(function (Uri $uri) {
            $this->assertStringEndsWith('/foo.html', $uri->getPath());
        });

        $this->api->downloadPrivate('bar.html');
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayHasKey('Authorization', $headers);
        });
    }

}
