<?php


namespace QCloudSDK\Image;


use QCloudSDK\Core\AbstractAPI;
use QCloudSDK\Core\BucketTrait;
use QCloudSDK\Facade\Config;
use QCloudSDK\Utils\Collection;
use QCloudSDK\Utils\Nonce;

abstract class API extends AbstractAPI
{
    const CONFIG_SECTION = 'image';

    const API_HOST = 'ApiHost';
    const API_SCHEME = 'ApiScheme';
    const APP_ID = 'AppId';
    const BUCKET = 'bucket';

    const DEFAULT_SCHEME = 'https';
    const DEFAULT_HOST = 'image.myqcloud.com';

    use BucketTrait;

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
     * @var Collection
     */
    protected $headers;
    /**
     * @var array
     */
    protected $params;

    protected function init()
    {
        // Common or COS-only
        $this->appSecretId = $this->getLocalConfig(Config::COMMON_SECRET_ID);
        $this->appSecretKey = $this->getLocalConfig(Config::COMMON_SECRET_KEY);
        // Only for COS
        $this->appId = $this->getLocalConfig(static::APP_ID);
        $this->bucket = $this->getLocalConfig(static::BUCKET);
        $this->setApiUrl();
        $this->headers = new Collection();
    }

    /**
     * @link https://cloud.tencent.com/document/product/460/6968
     * @param int $expire
     * @param string $file
     * @param int|null $time
     * @param string|null $rand
     * @return string
     */
    public function signMultiEffect(int $expire = 300, string $file = '', int $time = null, string $rand = null)
    {
        if(!isset($time)) $time = time();
        $params = [
            'a' => $this->appId,
            'b' => $this->bucket,
            'k' => $this->appSecretId,
            'e' => $expire ? $time + $expire : 0,
            't' => $time,
            'r' => $rand ?? Nonce::make(),
            'u' => 0,
            'f' => $file
        ];
        $toSignature = join('&', array_map(function($k, $v){
            return "$k=$v";
        }, array_keys($params), array_values($params)));
        return base64_encode(hash_hmac('SHA1', $toSignature, $this->appSecretKey, true) . $toSignature);
    }

}