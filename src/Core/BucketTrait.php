<?php


namespace QCloudSDK\Core;


trait BucketTrait
{
    /**
     * @var string
     */
    protected $bucket;
    /**
     * @var bool
     */
    protected $bucketInURL = true;

    abstract protected function setApiUrl();
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
        if($this->bucketInURL && $bucket !== $this->bucket){
            $this->bucket = $bucket;
            $this->setApiUrl();
        }
        return $this;
    }

}