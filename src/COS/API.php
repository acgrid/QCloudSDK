<?php


namespace QCloudSDK\COS;


use QCloudSDK\Core\AbstractAPI;
use QCloudSDK\Facade\Config;
use QCloudSDK\Utils\Nonce;

class API extends AbstractAPI
{
    const CONFIG_SECTION = 'cos';

    protected $apiUrl;

    protected $appId;

    protected $appSecretId;

    protected $appSecretKey;

    protected $appRegion;

    const API_URL = 'ApiUrl';
    const API_VERSION = 'ApiVersion';
    const APP_ID = 'AppId';
    const BUCKET = 'bucket';

    // USED FOR BUILD REQUEST
    /**
     * @var string
     */
    protected $bucket;
    /**
     * @var string
     */
    protected $op;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var array
     */
    protected $params;
    /**
     * @var array
     */
    protected $headers;

    protected function init()
    {
        // Common or COS-only
        $this->appSecretId = $this->getLocalConfig(Config::COMMON_SECRET_ID);
        $this->appSecretKey = $this->getLocalConfig(Config::COMMON_SECRET_KEY);
        $this->appRegion = $this->getLocalConfig(Config::COMMON_REGION);
        // Only for COS
        $this->appId = $this->getLocalConfig(static::APP_ID);
        $this->bucket = $this->getLocalConfig(static::BUCKET);
        $this->apiUrl = $this->getLocalConfig(static::API_URL, sprintf('https://%s-%s.cos%s.myqcloud.com/files/v%u', $this->bucket, $this->appId, $this->appRegion, $this->getLocalConfig(static::API_VERSION, 2)));
        $this->headers['Host'] = "{$this->appRegion}.file.myqcloud.com";
    }

    /**
     * @return string
     */
    public function getBucket(): string
    {
        return $this->bucket;
    }

    /**
     * @param string $bucket
     */
    public function setBucket(string $bucket)
    {
        $this->bucket = $bucket;
    }

    protected function doSign(int $expire, string $file = '')
    {
        $now = time();
        $params = [
            'a' => $this->appId,
            'b' => $this->bucket,
            'k' => $this->appSecretId,
            'e' => $now + $expire,
            't' => $now,
            'r' => Nonce::make(),
            'f' => join('/', array_map('urlencode', explode('/', $file)))
        ];
        $toSignature = join('&', array_map(function($k, $v){
            return "$k=$v";
        }, array_keys($params), array_values($params)));
        return base64_encode(hash_hmac('SHA1', $toSignature, true) . $toSignature);
    }

    public function signMultiEffect(int $ttl = 86400)
    {
        return $this->doSign($ttl);
    }

    public function signOnce(string $path)
    {
        return $this->doSign(0, "/{$this->appId}/{$this->bucket}/{$path}");
    }

    /**
     * @param string $op
     * @return API
     */
    protected function setOp(string $op): API
    {
        $this->op = $op;
        return $this;
    }

    /**
     * @param array $params
     * @return API
     */
    protected function setParams(array $params): API
    {
        $this->params = array_filter($params, function($value){
            return $value !== null;
        });
        return $this;
    }

    /**
     * @param array $headers
     * @return API
     */
    protected function setHeaders(array $headers): API
    {
        $this->headers = $headers;
        return $this;
    }

    protected function buildUrl()
    {
        return str_replace('//', '/', join('/', [$this->apiUrl, $this->bucket, $this->path]));
    }

    public function target(string $path)
    {
        $this->path = $path;
        unset($this->headers['Authorization']);
        return $this;
    }

    public function targetOnceSigned(string $path)
    {
        $this->target($path);
        $this->headers['Authorization'] = $this->signOnce($path);
        return $this;
    }

    public function targetSigned(string $path)
    {
        $this->target($path);
        $this->headers['Authorization'] = $this->signMultiEffect();
        return $this;
    }

    public function getQueryRequest()
    {
        return $this->parseJSON('request', $this->buildUrl(), 'GET', ['query' => $this->params + compact('op'), 'headers' => $this->headers]);
    }

    public function postJsonRequest()
    {
        return $this->parseJSON('request', $this->buildUrl(), 'POST', ['json' => $this->params + compact('op'), 'headers' => $this->headers]);
    }

    public function postFormDataRequest()
    {
        return $this->parseJSON('request', $this->buildUrl(), 'POST', ['multipart' => $this->params + compact('op'), 'headers' => $this->headers]);
    }

}