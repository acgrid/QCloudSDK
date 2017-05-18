<?php

namespace CDN;

use QCloudSDK\CDN\API;
use QCloudSDK\Facade\Config;
use QCloudSDKTests\TestCase;

class APITest extends TestCase
{
    /**
     * @var API
     */
    protected $api;

    protected function setUp()
    {
        parent::setUp();
        $this->api = new API(new Config([
            Config::COMMON_SECRET_ID => 'foo',
            Config::COMMON_SECRET_KEY => 'bar',
        ]), $this->http);
    }

    public function testHost()
    {
        $this->api->addCdnHost('www.example.com', 123, API::HOST_CNAME, ['1.2.3.4', '2.4.8.16']);
        $this->assertMyRequestBody(function($params){
            $this->assertContains('Action=AddCdnHost', $params);
            $this->assertContains('host=www.example.com', $params);
            $this->assertContains('origin=1.2.3.4%3B2.4.8.16', $params);
            $this->assertContains('projectId=123', $params);
            $this->assertContains('type=cname', $params);
        });

        $this->api->onlineHost('4444');
        $this->assertMyRequestBody(function($params){
            $this->assertContains('Action=OnlineHost', $params);
            $this->assertContains('hostId=4444', $params);
        });

        $this->api->offlineHost('6666');
        $this->assertMyRequestBody(function($params){
            $this->assertContains('Action=OfflineHost', $params);
            $this->assertContains('hostId=6666', $params);
        });

        $this->api->deleteCdnHost('9999');
        $this->assertMyRequestBody(function($params){
            $this->assertContains('Action=DeleteCdnHost', $params);
            $this->assertContains('hostId=9999', $params);
        });
        try{
            $this->api->updateCdnHost(2333, 'www.example.com', new \stdClass());
            $this->fail('Should throw an exception about origin');
        }catch (\InvalidArgumentException $e) {}

        $this->api->updateCdnHost(2333, 'www.example.com', '2.3.3.3');
        $this->assertMyRequestBody(function($params){
            $this->assertContains('Action=UpdateCdnHost', $params);
            $this->assertContains('hostId=2333', $params);
            $this->assertContains('host=www.example.com', $params);
            $this->assertContains('origin=2.3.3.3', $params);
        });

        $this->api->updateCdnConfig(2443, 343, [
            API::CONFIG_CACHE => $cache = [[0, "all", 1000], [1, ".jpg;.js", 2000], [2, "/www/html", 3000]],
            API::CONFIG_REFER => $refer = [1, ["qq.baidu.com", "*.baidu.com"]],
            API::CONFIG_FULL_URL => false,
            API::CONFIG_CACHE_MODE => API::CACHE_MODE_CUSTOM,
        ]);
        $this->assertMyRequestBody(function($params) use ($cache, $refer) {
            $this->assertContains('Action=UpdateCdnConfig', $params);
            $this->assertContains('hostId=2443', $params);
            $this->assertContains('projectId=343', $params);
            $this->assertContains('cache=' . urlencode(json_encode($cache)), $params);
            $this->assertContains('refer=' . urlencode(json_encode($refer)), $params);
            $this->assertContains('cacheMode=custom', $params);
            $this->assertContains('fullUrl=off', $params);
        });

        $this->api->updateCache(2443, $cache);
        $this->assertMyRequestBody(function($params) use ($cache) {
            $this->assertContains('Action=UpdateCache', $params);
            $this->assertContains('hostId=2443', $params);
            $this->assertContains('cache=' . urlencode(json_encode($cache)), $params);
        });

        $this->api->updateCdnProject(2443, 9899);
        $this->assertMyRequestBody(function($params) use ($cache) {
            $this->assertContains('Action=UpdateCdnProject', $params);
            $this->assertContains('hostId=2443', $params);
            $this->assertContains('projectId=9899', $params);
        });

        $this->api->getHostInfoById([341, 532]);
        $this->assertMyRequestBody(function($params) use ($cache) {
            $this->assertContains('Action=GetHostInfoById', $params);
            $this->assertContains('ids.0=341', $params);
            $this->assertContains('ids.1=532', $params);
        });

        $this->api->getHostInfoByHost('foobar.com');
        $this->assertMyRequestBody(function($params) use ($cache) {
            $this->assertContains('Action=GetHostInfoByHost', $params);
            $this->assertContains('hosts.0=foobar.com', $params);
        });

        $this->api->describeCdnHosts(10, 15);
        $this->assertMyRequestBody(function($params) use ($cache) {
            $this->assertContains('Action=DescribeCdnHosts', $params);
            $this->assertContains('offset=10', $params);
            $this->assertContains('limit=15', $params);
        });

    }

    public function testStat()
    {
        $this->api->getCdnStatusCode('2017-01-01 12:00:00', '2017-01-15', [3, 4], null, 10);
        $this->assertMyRequestBody(function($params) {
            $this->assertContains('Action=GetCdnStatusCode', $params);
            $this->assertContains('startDate=2017-01-01', $params);
            $this->assertContains('endDate=2017-01-15', $params);
            $this->assertContains('projects.0=3', $params);
            $this->assertContains('projects.1=4', $params);
            $this->assertNotContains('hosts.0=', $params);
            $this->assertContains('period=10', $params);
        });

        $this->api->GetCdnStatTop('2017-01-01 12:00:00', '2017-01-15', API::STAT_FLUX, [3, 4], [1], 10);
        $this->assertMyRequestBody(function($params) {
            $this->assertContains('Action=GetCdnStatTop', $params);
            $this->assertContains('startDate=2017-01-01', $params);
            $this->assertContains('endDate=2017-01-15', $params);
            $this->assertContains('statType=flux', $params);
            $this->assertContains('projects.0=3', $params);
            $this->assertContains('projects.1=4', $params);
            $this->assertContains('hosts.0=1', $params);
            $this->assertContains('period=10', $params);
        });

        $this->api->describeCdnHostInfo('2017-01-01 12:00:00', '2017-01-15', API::STAT_FLUX, [3, 4], [1]);
        $this->assertMyRequestBody(function($params) {
            $this->assertContains('Action=DescribeCdnHostInfo', $params);
            $this->assertContains('startDate=2017-01-01', $params);
            $this->assertContains('endDate=2017-01-15', $params);
            $this->assertContains('statType=flux', $params);
            $this->assertContains('projects.0=3', $params);
            $this->assertContains('projects.1=4', $params);
            $this->assertContains('hosts.0=1', $params);
        });

        $this->api->describeCdnHostDetailedInfo('2017-01-01 12:00:00', '2017-01-15', API::STAT_FLUX, [3, 4], [1]);
        $this->assertMyRequestBody(function($params) {
            $this->assertContains('Action=DescribeCdnHostDetailedInfo', $params);
            $this->assertContains('startDate=2017-01-01', $params);
            $this->assertContains('endDate=2017-01-15', $params);
            $this->assertContains('statType=flux', $params);
            $this->assertContains('projects.0=3', $params);
            $this->assertContains('projects.1=4', $params);
            $this->assertContains('hosts.0=1', $params);
        });

    }

    public function testRefresh()
    {
        $this->api->getCdnRefreshLog([API::QUERY_START => '2017-01-01', API::QUERY_END => '2017-01-10', API::QUERY_ID => 41, API::QUERY_URL => 'foo']);
        $this->assertMyRequestBody(function($params) {
            $this->assertContains('Action=GetCdnRefreshLog', $params);
            $this->assertContains('startDate=2017-01-01', $params);
            $this->assertContains('endDate=2017-01-10', $params);
            $this->assertContains('taskId=41', $params);
            $this->assertNotContains('url=', $params);
        });

        $this->api->getCdnRefreshLog([API::QUERY_ID => 'foo', API::QUERY_URL => $url = 'https://www.example.com/']);
        $this->assertMyRequestBody(function($params) use ($url) {
            $this->assertContains('Action=GetCdnRefreshLog', $params);
            $this->assertNotContains('taskId=', $params);
            $this->assertContains('url=' . urlencode($url), $params);
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
            $this->assertContains('Action=RefreshCdnUrl', $params);
            $this->assertContains('urls.0=' . urlencode($urls[0]), $params);
            $this->assertContains('urls.1=' . urlencode($urls[1]), $params);
        });

        $this->api->refreshCdnDir($dirs = ['https://foo.com/', 'http://www.example.org/foo']);
        $this->assertMyRequestBody(function($params) use ($dirs) {
            $this->assertContains('Action=RefreshCdnDir', $params);
            $this->assertContains('dirs.0=' . urlencode($dirs[0]), $params);
            $this->assertContains('dirs.1=' . urlencode($dirs[1]), $params);
        });

    }

    public function testFetchLog()
    {
        $this->api->getCdnLogList(1323, '2017-01-12');
        $this->assertMyRequestBody(function($params) {
            $this->assertContains('Action=GetCdnLogList', $params);
            $this->assertContains('host=1323', $params);
            $this->assertNotContains('startDate=', $params);
        });

        $this->api->getCdnLogList(1323, '2017-01-12', '2017-01-20');
        $this->assertMyRequestBody(function($params) {
            $this->assertContains('startDate=2017-01-12', $params);
            $this->assertContains('endDate=2017-01-20', $params);
        });
    }

}
