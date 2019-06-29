<?php


namespace QCloudSDK\Core;


use QCloudSDK\Facade\Config;

trait GeneralSignatureTrait
{
    /**
     * @var Config
     */
    protected $config;

    use NonceTrait;

    protected function doSign(string $method, string $endpoint, array $params)
    {
        if(!isset($params['Nonce'])) $params['Nonce'] = $this->makeNonce();
        if(!isset($params['Timestamp'])) $params['Timestamp'] = time();
        $params['SecretId'] = $this->config->get(Config::COMMON_SECRET_ID);
        ksort($params);
        $toSignature = strtoupper($method) . "$endpoint?" . join('&', array_map(function($key, $value){
            return str_replace('_', '.', $key) . "=$value";
        }, array_keys($params), array_values($params)));
        $params['Signature'] = base64_encode(hash_hmac('sha1', $toSignature, $this->config->get(Config::COMMON_SECRET_KEY), true));
        return $params;
    }

}