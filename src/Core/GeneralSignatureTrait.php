<?php


namespace QCloudSDK\Core;


use Tightenco\Collect\Support\Arr;

trait GeneralSignatureTrait
{
    protected $config;

    use NonceTrait;

    protected function doSign(string $method, string $endpoint, array $params)
    {
        if(!isset($params['Nonce'])) $params['Nonce'] = $this->makeNonce();
        if(!isset($params['Timestamp'])) $params['Timestamp'] = time();
        $params['SecretId'] = Arr::get($this->config, CommonConfiguration::CONFIG_SECRET_ID);
        ksort($params);
        $toSignature = strtoupper($method) . "$endpoint?" . join('&', array_map(function($key, $value){
            return str_replace('_', '.', $key) . "=$value";
        }, array_keys($params), array_values($params)));
        $params['Signature'] = base64_encode(hash_hmac('sha1', $toSignature, Arr::get($this->config, CommonConfiguration::CONFIG_SECRET_KEY), true));
        return $params;
    }

}