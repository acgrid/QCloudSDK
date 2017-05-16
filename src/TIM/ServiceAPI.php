<?php


namespace QCloudSDK\TIM;


abstract class ServiceAPI extends API
{
    const SERVICE_ENDPOINT = 'ServiceEndpoint';

    protected function init()
    {
        parent::init();
        $this->endpoint = $this->endpoint . $this->getLocalConfig(self::SERVICE_ENDPOINT, 'v5/tlssmssvr/');
    }

}