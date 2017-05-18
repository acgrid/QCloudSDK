<?php


namespace QCloudSDK\COS;


use QCloudSDK\Core\AbstractAPI;
use QCloudSDK\Facade\Config;
use QCloudSDK\Utils\Collection;
use QCloudSDK\Utils\Nonce;

class API extends AbstractAPI
{
    const CONFIG_SECTION = 'cos';

    const API_URL = 'ApiUrl';
    const API_VERSION = 'ApiVersion';
    const APP_ID = 'AppId';
    const BUCKET = 'bucket';
    /**
     * @var string
     */
    protected $apiUrl;
    /**
     * @var string
     */
    protected $appId;
    /**
     * @var string
     */
    protected $appSecretId;
    /**
     * @var string
     */
    protected $appSecretKey;
    /**
     * @var string
     */
    protected $appRegion;
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
     * @var Collection
     */
    protected $headers;

    const ATTR_AUTHORITY = 'authority';
    const ATTR_HEADERS = 'custom_headers';

    const AUTH_INVALID = 'eInvalid';
    const AUTH_WR_PRIVATE = 'eWRPrivate';
    const AUTH_W_PRIVATE_R_PUBLIC = 'WPrivateRPublic';

    protected function init()
    {
        // Common or COS-only
        $this->appSecretId = $this->getLocalConfig(Config::COMMON_SECRET_ID);
        $this->appSecretKey = $this->getLocalConfig(Config::COMMON_SECRET_KEY);
        $this->appRegion = $this->getLocalConfig(Config::COMMON_REGION);
        // Only for COS
        $this->appId = $this->getLocalConfig(static::APP_ID);
        $this->bucket = $this->getLocalConfig(static::BUCKET);
        $this->setApiUrl();
        $this->headers = new Collection(['Host' => "{$this->appRegion}.file.myqcloud.com"]);
    }

    protected function setApiUrl()
    {
        $this->apiUrl = $this->getLocalConfig(static::API_URL, sprintf('https://%s-%s.cos%s.myqcloud.com/files/v%u', $this->bucket, $this->appId, $this->appRegion, $this->getLocalConfig(static::API_VERSION, 2)));
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
     * @return $this
     */
    public function setBucket(string $bucket)
    {
        if($bucket !== $this->bucket){
            $this->bucket = $bucket;
            $this->setApiUrl();
        }
        return $this;
    }

    /**
     * @return Collection
     */
    public function getHeaders(): Collection
    {
        return $this->headers;
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6054
     * @param int $expire
     * @param string $file
     * @param int|null $time
     * @param string|null $rand
     * @return string
     */
    protected function doSign(int $expire, string $file = '', int $time = null, string $rand = null)
    {
        if(!isset($time)) $time = time();
        $params = [
            'a' => $this->appId,
            'b' => $this->bucket,
            'k' => $this->appSecretId,
            'e' => $expire ? $time + $expire : 0,
            't' => $time,
            'r' => $rand ?? Nonce::make(),
            'f' => join('/', array_map('urlencode', explode('/', $file)))
        ];
        $toSignature = join('&', array_map(function($k, $v){
            return "$k=$v";
        }, array_keys($params), array_values($params)));
        return base64_encode(hash_hmac('SHA1', $toSignature, $this->appSecretKey, true) . $toSignature);
    }

    /**
     * @param int $ttl
     * @param int|null $time
     * @param string|null $rand
     * @return string
     */
    public function signMultiEffect(int $ttl = 86400, int $time = null, string $rand = null)
    {
        return $this->doSign($ttl, '', $time, $rand);
    }

    /**
     * @param string $path
     * @param int|null $time
     * @param string|null $rand
     * @return string
     */
    public function signOnce(string $path, int $time = null, string $rand = null)
    {
        return $this->doSign(0, "/{$this->appId}/{$this->bucket}/{$path}", $time, $rand);
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

    protected function buildUrl()
    {
        return str_replace('//', '/', join('/', [$this->apiUrl, $this->bucket, $this->path]));
    }

    public function target(string $path)
    {
        $this->path = $path;
        $this->headers->forget('Authorization');
        return $this;
    }

    public function targetOnceSigned(string $path)
    {
        $this->target($path);
        $this->headers->set('Authorization', $this->signOnce($path));
        return $this;
    }

    public function targetSigned(string $path)
    {
        $this->target($path);
        $this->headers->set('Authorization', $this->signMultiEffect());
        return $this;
    }

    protected function request(string $method, string $paramOption)
    {
        $params = $this->params ?? [];
        if($paramOption !== 'multipart') $params += ['op' => $this->op];
        return $this->parseJSON('request', $this->buildUrl(), $method, [$paramOption => $params, 'headers' => $this->headers->all()]);
    }

    public function getQueryRequest()
    {
        return $this->request('GET','query');
    }

    public function postJsonRequest()
    {
        return $this->request('POST','json');
    }

    public function postFormDataRequest()
    {
        return $this->request('POST','multipart');
    }

}