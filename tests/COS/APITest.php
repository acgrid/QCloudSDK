<?php

namespace QCloudSDKTests\COS;

use QCloudSDK\COS\API;
use QCloudSDK\Utils\Collection;
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
        $this->api = new API($this->configForTest(), $this->http);
    }

    public function testUtilities()
    {
        $this->assertSame('newbucket', $this->api->getBucket());
        $this->assertSame($this->api, $this->api->setBucket('mybucket'));
        $this->assertInstanceOf(Collection::class, $this->api->getHeaders());
    }
}
