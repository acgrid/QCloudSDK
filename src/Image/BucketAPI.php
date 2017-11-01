<?php


namespace QCloudSDK\Image;


class BucketAPI extends API
{
    /**
     * @var string
     */
    protected $path;

    protected function setApiUrl()
    {
        $this->apiUrl = sprintf('%s://%s-%s.%s/',
            $this->getLocalConfig(static::API_SCHEME, static::DEFAULT_SCHEME), $this->bucket, $this->appId,
            $this->getLocalConfig(static::API_SCHEME, static::DEFAULT_HOST));
    }

    /**
     * @param string $file
     * @param int|null $time
     * @param string|null $rand
     * @return string
     */
    public function signOnce(string $file, int $time = null, string $rand = null)
    {
        return $this->signMultiEffect(0, $file, $time, $rand);
    }

}