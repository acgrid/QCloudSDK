<?php


namespace QCloudSDKTests\Image;


use QCloudSDK\Core\CommonConfiguration;
use QCloudSDK\COS\API;
use QCloudSDK\Image\Processor;
use QCloudSDKTests\TestCase;

class ImageTestCase extends TestCase
{
    const EXAMPLE_CONFIG = [
        CommonConfiguration::CONFIG_SECRET_ID => 'AKIDgaoOYh2kOmJfWVdH4lpfxScG2zPLPGoK',
        CommonConfiguration::CONFIG_SECRET_KEY => 'nwOKDouy5JctNOlnere4gkVoOUz5EYAb',
        API::CONFIG_APP_ID => '1252821871',
        API::CONFIG_BUCKET => 'tencentyun',
        API::CONFIG_REGION => 'gz',
        Processor::CONFIG_API_SCHEME => 'http',
        Processor::CONFIG_API_HOST => 'test.image.myqcloud.com',
        Processor::CONFIG_PRIVATE => 60,
        Processor::CONFIG_STYLE_SEPARATOR => '!',
    ];

}