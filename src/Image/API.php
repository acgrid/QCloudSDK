<?php


namespace QCloudSDK\Image;


use QCloudSDK\Core\AbstractAPI;
use QCloudSDK\Core\BucketTrait;
use QCloudSDK\Core\CommonConfiguration;
use QCloudSDK\Core\NonceTrait;
use QCloudSDK\COS\API as CosAPI;
use Tightenco\Collect\Support\Arr;
use Tightenco\Collect\Support\Collection;

abstract class API extends AbstractAPI
{

    const CONFIG_API_HOST = 'ApiHost';
    const CONFIG_API_SCHEME = 'ApiScheme';

    const DEFAULT_SCHEME = 'https';
    const DEFAULT_HOST = 'image.myqcloud.com';

    use BucketTrait;
    use NonceTrait;

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
     * @var array|string
     */
    protected $params;

    protected function init()
    {
        // Common or COS-only
        $this->appSecretId = Arr::get($this->config, CommonConfiguration::CONFIG_SECRET_ID);
        $this->appSecretKey = Arr::get($this->config, CommonConfiguration::CONFIG_SECRET_KEY);
        // Only for COS
        $this->appId = Arr::get($this->config, CosAPI::CONFIG_APP_ID);
        $this->bucket = Arr::get($this->config, CosAPI::CONFIG_BUCKET);
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
            'r' => $rand ?? $this->makeNonce(),
            'u' => 0,
            'f' => $file
        ];
        $toSignature = join('&', array_map(function($k, $v){
            return "$k=$v";
        }, array_keys($params), array_values($params)));
        return base64_encode(hash_hmac('SHA1', $toSignature, $this->appSecretKey, true) . $toSignature);
    }

}