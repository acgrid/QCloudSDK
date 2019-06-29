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

    protected function setUp(): void
    {
        parent::setUp();
        $this->api = new File(APITest::EXAMPLE_CONFIG, $this->http, $this->logger);
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
            $this->assertStringContainsString($this->makeFormData('op', 'upload'), $body);
            $this->assertStringContainsString($this->makeFormData('filecontent', $content), $body);
            $this->assertStringContainsString($this->makeFormData('sha1', sha1($content)), $body);
            $this->assertStringContainsString($this->makeFormData('biz_attr', 'bar'), $body);
            $this->assertStringContainsString($this->makeFormData('insertOnly', '1'), $body);
        });

        $this->api->uploadFile('bar.php', __FILE__);
        $this->assertMyRequestBody(function ($body) use ($content) {
            $this->assertStringContainsString($this->makeFormData('op', 'upload'), $body);
            $this->assertStringContainsString($this->makeFormData('filecontent', file_get_contents(__FILE__), basename(__FILE__)), $body);
            $this->assertStringContainsString($this->makeFormData('sha1', sha1_file(__FILE__)), $body);
            $this->assertStringNotContainsString("name=\"biz_attr\"", $body); // Escape myself
            $this->assertStringNotContainsString("name=\"insertOnly\"", $body);
        });
    }

    public function testUploadSlice()
    {
        $this->api->uploadSliceInit('foo.txt', 9543565441, File::SLICE_1MB);
        $this->assertMyRequestBody(function ($body) {
            $this->assertStringContainsString($this->makeFormData('op', 'upload_slice_init'), $body);
            $this->assertStringContainsString($this->makeFormData('filesize', 9543565441), $body);
            $this->assertStringContainsString($this->makeFormData('slice_size', File::SLICE_1MB), $body);
        });

        $this->api->uploadSliceData('foo.txt', 'foo', '641151102', 1048576);
        $this->assertMyRequestBody(function ($body) {
            $this->assertStringContainsString($this->makeFormData('op', 'upload_slice_data'), $body);
            $this->assertStringContainsString($this->makeFormData('filecontent', 'foo'), $body);
            $this->assertStringContainsString($this->makeFormData('session', '641151102'), $body);
            $this->assertStringContainsString($this->makeFormData('offset', 1048576), $body);
        });

        $this->api->uploadSliceFinish('foo.txt', '641151102', 9543565441);
        $this->assertMyRequestBody(function ($body) {
            $this->assertStringContainsString($this->makeFormData('op', 'upload_slice_finish'), $body);
            $this->assertStringContainsString($this->makeFormData('filesize', 9543565441), $body);
            $this->assertStringContainsString($this->makeFormData('session', '641151102'), $body);
        });

        $this->api->uploadSliceList('foo.txt');
        $this->assertMyRequestMethod('POST');
        $this->assertMyRequestBody(function ($body) {
            $this->assertStringContainsString($this->makeFormData('op', 'upload_slice_list'), $body);
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
            $this->assertStringContainsString($this->makeFormData('op', 'move'), $body);
            $this->assertStringContainsString($this->makeFormData('dest_fileid', 'b.txt'), $body);
            $this->assertStringContainsString($this->makeFormData('to_over_write', '0'), $body);
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
            $this->assertStringContainsString($this->makeFormData('op', 'copy'), $body);
            $this->assertStringContainsString($this->makeFormData('dest_fileid', 'a.txt'), $body);
            $this->assertStringContainsString($this->makeFormData('to_over_write', '1'), $body);
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
        $this->assertMyRequestUri(function (Uri $uri) {
            $this->assertSame('https://newbucket-200001.cosgz.myqcloud.com/foo.html', $uri->__toString());
        });
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayNotHasKey('Authorization', $headers);
            $this->assertSame('newbucket-200001.cosgz.myqcloud.com', $headers['Host'][0]);
        });

        $this->api->downloadPrivate('bar.html', true);
        $this->assertMyRequestUri(function (Uri $uri) {
            $this->assertSame('https://newbucket-200001.file.myqcloud.com/bar.html', $uri->__toString());
        });
        $this->assertMyRequestHeaders(function ($headers) {
            $this->assertArrayHasKey('Authorization', $headers);
        });
    }

}
