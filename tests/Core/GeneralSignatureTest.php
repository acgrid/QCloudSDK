<?php

namespace QCloudSDKTests\Core;


use QCloudSDK\Core\ActionTrait;
use QCloudSDK\Core\GeneralSignatureTrait;
use QCloudSDK\Facade\Config;
use QCloudSDKTests\TestCase;

class GeneralSignatureTest extends TestCase
{
    use ActionTrait;
    use GeneralSignatureTrait;

    protected function setUp()
    {
        $this->config = new Config([
            Config::COMMON_SECRET_ID => 'AKIDT8G5AsY1D3MChWooNq1rFSw1fyBVCX9D',
            Config::COMMON_SECRET_KEY => 'pxPgRWDbCy86ZYyqBTDk7WmeRZSmPco0',
        ]);
    }

    /**
     * @link https://www.qcloud.com/document/api/228/1725
     */
    public function testSignature()
    {
        $params = $this->createAction('DescribeCdnHosts');
        $params['offset'] = 0;
        $params['limit'] = 10;
        $params['Timestamp'] = '1463122059';
        $params['Nonce'] = '13029';
        $signed = $this->doSign('get', 'cdn.api.qcloud.com/v2/index.php', $params);
        $this->assertArrayHasKey('Signature', $signed);
        $this->assertSame('bWMMAR1eFGjZ5KWbfxTlBiLiNLc=', $signed['Signature']);
    }

    public function testReplay()
    {
        $params = $this->createAction('DescribeCdnHosts');
        $signed = $this->doSign('get', 'cdn.api.qcloud.com/v2/index.php', $params);
        $this->assertArrayHasKey('Timestamp', $signed);
        $this->assertArrayHasKey('Nonce', $signed);
        $this->assertEquals(time(), $signed['Timestamp'], 'Timestamp is not generated as now.', 1);
        $this->assertRegExp('/\d{5}/', $signed['Nonce']);
    }

}
