<?php


namespace QCloudSDK\TIM;


use Tightenco\Collect\Support\Arr;

abstract class SmsAPI extends API
{
    const CONFIG_ENDPOINT = 'SmsEndpoint';

    protected function init()
    {
        parent::init();
        $this->endpoint = $this->endpoint . Arr::get($this->config, self::CONFIG_ENDPOINT, 'v5/tlssmssvr/');
    }

}