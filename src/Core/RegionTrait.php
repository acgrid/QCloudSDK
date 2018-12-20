<?php


namespace QCloudSDK\Core;


trait RegionTrait
{
    /**
     * @var string
     */
    protected $appRegion;
    /**
     * @var bool
     */
    protected $regionInURL = true;

    abstract protected function setApiUrl();
    /**
     * @return string
     */
    public function getRegion(): string
    {
        return $this->appRegion;
    }

    /**
     * @param string $region
     * @return $this
     */
    public function setRegion(string $region)
    {
        if($this->regionInURL && $region !== $this->appRegion){
            $this->appRegion = $region;
            $this->setApiUrl();
        }
        return $this;
    }

}