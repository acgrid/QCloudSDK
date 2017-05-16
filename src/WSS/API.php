<?php


namespace QCloudSDK\WSS;


use QCloudSDK\Core\AbstractAPI;
use QCloudSDK\Core\ActionTrait;
use QCloudSDK\Core\GeneralSignatureTrait;
use QCloudSDK\Facade\Config;
use QCloudSDK\Utils\Collection;

class API extends AbstractAPI
{
    use ActionTrait;
    use GeneralSignatureTrait;

    const TYPE_CLIENT = 'CA';

    const TYPE_SERVER = 'SVR';

    protected function request(array $params)
    {
        return $this->parseJSONSigned('post', $this->getLocalConfig(Config::MODULE_COMMON_ENDPOINT, 'wss.api.qcloud.com/v2/index.php'), $params);
    }

    /**
     * @link https://www.qcloud.com/document/api/400/9078
     * @param string $certType
     * @param string $cert
     * @param string|null $key
     * @param string|null $alias
     * @return Collection
     */
    protected function uploadCert(string $certType, string $cert, string $key = null, string $alias = null)
    {
        $params = array_filter(compact('cert', 'certType', 'key', 'alias')) + $this->createAction('CertUpload');
        return $this->request($params);
    }

    public function uploadServerCert(string $cert, string $key, string $alias = null)
    {
        return $this->uploadCert(static::TYPE_SERVER, $cert, $key, $alias);
    }

    public function uploadClientCert(string $cert, string $key = null, string $alias = null)
    {
        return $this->uploadCert(static::TYPE_CLIENT, $cert, $key, $alias);
    }

    public function ensureUploaded(Collection $collection)
    {
        return $this->expectResult('data', $collection);
    }

}