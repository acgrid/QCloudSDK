<?php

namespace QCloudSDKTests\CDN;

use QCloudSDK\CDN\API;
use QCloudSDKTests\TestCase;

class APITest extends TestCase
{
    /**
     * @var API
     */
    protected $api;

    protected function setUp(): void
    {
        parent::setUp();
        $this->api = new API(static::EXAMPLE_CONFIG, $this->http, $this->logger);
    }

    public function testHost()
    {
        $this->api->addCdnHost('www.example.com', 123, API::HOST_CNAME, ['1.2.3.4', '2.4.8.16']);
        $this->assertMyRequestBody(function($params){
            $this->assertStringContainsString('Action=AddCdnHost', $params);
            $this->assertStringContainsString('host=www.example.com', $params);
            $this->assertStringContainsString('origin=1.2.3.4%3B2.4.8.16', $params);
            $this->assertStringContainsString('projectId=123', $params);
            $this->assertStringContainsString('type=cname', $params);
        });

        $this->api->onlineHost('4444');
        $this->assertMyRequestBody(function($params){
            $this->assertStringContainsString('Action=OnlineHost', $params);
            $this->assertStringContainsString('hostId=4444', $params);
        });

        $this->api->offlineHost('6666');
        $this->assertMyRequestBody(function($params){
            $this->assertStringContainsString('Action=OfflineHost', $params);
            $this->assertStringContainsString('hostId=6666', $params);
        });

        $this->api->deleteCdnHost('9999');
        $this->assertMyRequestBody(function($params){
            $this->assertStringContainsString('Action=DeleteCdnHost', $params);
            $this->assertStringContainsString('hostId=9999', $params);
        });
        try{
            $this->api->updateCdnHost(2333, 'www.example.com', new \stdClass());
            $this->fail('Should throw an exception about origin');
        }catch (\InvalidArgumentException $e) {}

        $this->api->updateCdnHost(2333, 'www.example.com', '2.3.3.3');
        $this->assertMyRequestBody(function($params){
            $this->assertStringContainsString('Action=UpdateCdnHost', $params);
            $this->assertStringContainsString('hostId=2333', $params);
            $this->assertStringContainsString('host=www.example.com', $params);
            $this->assertStringContainsString('origin=2.3.3.3', $params);
        });

        $this->api->updateCdnConfig(2443, 343, [
            API::CONFIG_CACHE => $cache = [[0, "all", 1000], [1, ".jpg;.js", 2000], [2, "/www/html", 3000]],
            API::CONFIG_REFER => $refer = [1, ["qq.baidu.com", "*.baidu.com"]],
            API::CONFIG_FULL_URL => false,
            API::CONFIG_CACHE_MODE => API::CACHE_MODE_CUSTOM,
        ]);
        $this->assertMyRequestBody(function($params) use ($cache, $refer) {
            $this->assertStringContainsString('Action=UpdateCdnConfig', $params);
            $this->assertStringContainsString('hostId=2443', $params);
            $this->assertStringContainsString('projectId=343', $params);
            $this->assertStringContainsString('cache=' . urlencode(json_encode($cache)), $params);
            $this->assertStringContainsString('refer=' . urlencode(json_encode($refer)), $params);
            $this->assertStringContainsString('cacheMode=custom', $params);
            $this->assertStringContainsString('fullUrl=off', $params);
        });

        $this->api->updateCache(2443, $cache);
        $this->assertMyRequestBody(function($params) use ($cache) {
            $this->assertStringContainsString('Action=UpdateCache', $params);
            $this->assertStringContainsString('hostId=2443', $params);
            $this->assertStringContainsString('cache=' . urlencode(json_encode($cache)), $params);
        });

        $this->api->updateCdnProject(2443, 9899);
        $this->assertMyRequestBody(function($params) use ($cache) {
            $this->assertStringContainsString('Action=UpdateCdnProject', $params);
            $this->assertStringContainsString('hostId=2443', $params);
            $this->assertStringContainsString('projectId=9899', $params);
        });

        $this->api->getHostInfoById([341, 532]);
        $this->assertMyRequestBody(function($params) use ($cache) {
            $this->assertStringContainsString('Action=GetHostInfoById', $params);
            $this->assertStringContainsString('ids.0=341', $params);
            $this->assertStringContainsString('ids.1=532', $params);
        });

        $this->api->getHostInfoByHost('foobar.com');
        $this->assertMyRequestBody(function($params) use ($cache) {
            $this->assertStringContainsString('Action=GetHostInfoByHost', $params);
            $this->assertStringContainsString('hosts.0=foobar.com', $params);
        });

        $this->api->describeCdnHosts(10, 15);
        $this->assertMyRequestBody(function($params) use ($cache) {
            $this->assertStringContainsString('Action=DescribeCdnHosts', $params);
            $this->assertStringContainsString('offset=10', $params);
            $this->assertStringContainsString('limit=15', $params);
        });

    }

    public function testStat()
    {
        $this->api->getCdnStatusCode('2017-01-01 12:00:00', '2017-01-15', [3, 4], null, 10);
        $this->assertMyRequestBody(function($params) {
            $this->assertStringContainsString('Action=GetCdnStatusCode', $params);
            $this->assertStringContainsString('startDate=2017-01-01', $params);
            $this->assertStringContainsString('endDate=2017-01-15', $params);
            $this->assertStringContainsString('projects.0=3', $params);
            $this->assertStringContainsString('projects.1=4', $params);
            $this->assertStringNotContainsString('hosts.0=', $params);
            $this->assertStringContainsString('period=10', $params);
        });

        $this->api->GetCdnStatTop('2017-01-01 12:00:00', '2017-01-15', API::STAT_FLUX, [3, 4], [1], 10);
        $this->assertMyRequestBody(function($params) {
            $this->assertStringContainsString('Action=GetCdnStatTop', $params);
            $this->assertStringContainsString('startDate=2017-01-01', $params);
            $this->assertStringContainsString('endDate=2017-01-15', $params);
            $this->assertStringContainsString('statType=flux', $params);
            $this->assertStringContainsString('projects.0=3', $params);
            $this->assertStringContainsString('projects.1=4', $params);
            $this->assertStringContainsString('hosts.0=1', $params);
            $this->assertStringContainsString('period=10', $params);
        });

        $this->api->describeCdnHostInfo('2017-01-01 12:00:00', '2017-01-15', API::STAT_FLUX, [3, 4], [1]);
        $this->assertMyRequestBody(function($params) {
            $this->assertStringContainsString('Action=DescribeCdnHostInfo', $params);
            $this->assertStringContainsString('startDate=2017-01-01', $params);
            $this->assertStringContainsString('endDate=2017-01-15', $params);
            $this->assertStringContainsString('statType=flux', $params);
            $this->assertStringContainsString('projects.0=3', $params);
            $this->assertStringContainsString('projects.1=4', $params);
            $this->assertStringContainsString('hosts.0=1', $params);
        });

        $this->api->describeCdnHostDetailedInfo('2017-01-01 12:00:00', '2017-01-15', API::STAT_FLUX, [3, 4], [1]);
        $this->assertMyRequestBody(function($params) {
            $this->assertStringContainsString('Action=DescribeCdnHostDetailedInfo', $params);
            $this->assertStringContainsString('startDate=2017-01-01', $params);
            $this->assertStringContainsString('endDate=2017-01-15', $params);
            $this->assertStringContainsString('statType=flux', $params);
            $this->assertStringContainsString('projects.0=3', $params);
            $this->assertStringContainsString('projects.1=4', $params);
            $this->assertStringContainsString('hosts.0=1', $params);
        });

    }

    public function testRefresh()
    {
        $this->api->getCdnRefreshLog([API::QUERY_START => '2017-01-01', API::QUERY_END => '2017-01-10', API::QUERY_ID => 41, API::QUERY_URL => 'foo']);
        $this->assertMyRequestBody(function($params) {
            $this->assertStringContainsString('Action=GetCdnRefreshLog', $params);
            $this->assertStringContainsString('startDate=2017-01-01', $params);
            $this->assertStringContainsString('endDate=2017-01-10', $params);
            $this->assertStringContainsString('taskId=41', $params);
            $this->assertStringNotContainsString('url=', $params);
        });

        $this->api->getCdnRefreshLog([API::QUERY_ID => 'foo', API::QUERY_URL => $url = 'https://www.example.com/']);
        $this->assertMyRequestBody(function($params) use ($url) {
            $this->assertStringContainsString('Action=GetCdnRefreshLog', $params);
            $this->assertStringNotContainsString('taskId=', $params);
            $this->assertStringContainsString('url=' . urlencode($url), $params);
        });

        try{
            $this->api->getCdnRefreshLog([]);
            $this->fail('Should throw an exception on empty condition');
        }catch (\InvalidArgumentException $e) {}
        try{
            $this->api->getCdnRefreshLog([API::QUERY_END => '2017-01-10']);
            $this->fail('Should throw an exception on incomplete condition');
        }catch (\InvalidArgumentException $e) {}

        try{
            $this->api->refreshCdnUrl(['https://foo.com/', 'www.example.org']);
            $this->fail('Should throw an exception on URL without a scheme');
        }catch (\InvalidArgumentException $e) {}

        $this->api->refreshCdnUrl($urls = ['https://foo.com/', 'http://www.example.org/v/i.txt']);
        $this->assertMyRequestBody(function($params) use ($urls) {
            $this->assertStringContainsString('Action=RefreshCdnUrl', $params);
            $this->assertStringContainsString('urls.0=' . urlencode($urls[0]), $params);
            $this->assertStringContainsString('urls.1=' . urlencode($urls[1]), $params);
        });

        $this->api->refreshCdnDir($dirs = ['https://foo.com/', 'http://www.example.org/foo']);
        $this->assertMyRequestBody(function($params) use ($dirs) {
            $this->assertStringContainsString('Action=RefreshCdnDir', $params);
            $this->assertStringContainsString('dirs.0=' . urlencode($dirs[0]), $params);
            $this->assertStringContainsString('dirs.1=' . urlencode($dirs[1]), $params);
        });

    }

    public function testFetchLog()
    {
        $this->api->getCdnLogList(1323, '2017-01-12');
        $this->assertMyRequestBody(function($params) {
            $this->assertStringContainsString('Action=GetCdnLogList', $params);
            $this->assertStringContainsString('host=1323', $params);
            $this->assertStringNotContainsString('startDate=', $params);
        });

        $this->api->getCdnLogList(1323, '2017-01-12', '2017-01-20');
        $this->assertMyRequestBody(function($params) {
            $this->assertStringContainsString('startDate=2017-01-12', $params);
            $this->assertStringContainsString('endDate=2017-01-20', $params);
        });
    }

}
