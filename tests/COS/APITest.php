<?php

namespace QCloudSDKTests\COS;

use QCloudSDK\Core\CommonConfiguration;
use QCloudSDK\COS\API;
use QCloudSDKTests\TestCase;
use Tightenco\Collect\Support\Collection;

class APITest extends TestCase
{
    /**
     * @var API
     */
    protected $api;

    const EXAMPLE_CONFIG = [
        CommonConfiguration::CONFIG_SECRET_ID => 'AKIDUfLUEUigQiXqm7CVSspKJnuaiIKtxqAv',
        CommonConfiguration::CONFIG_SECRET_KEY => 'bLcPnl88WU30VY57ipRhSePfPdOfSruK',
        API::CONFIG_APP_ID => '200001',
        API::CONFIG_BUCKET => 'newbucket',
        API::CONFIG_REGION => 'gz',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->api = new API(static::EXAMPLE_CONFIG, $this->http, $this->logger);
    }

    public function testUtilities()
    {
        $this->assertSame('newbucket', $this->api->getBucket());
        $this->assertSame($this->api, $this->api->setBucket('mybucket'));
        $this->assertSame($this->api, $this->api->setRegion('cd'));
        $this->assertSame('cd', $this->api->getRegion());
        $this->assertInstanceOf(Collection::class, $this->api->getHeaders());
    }

}
