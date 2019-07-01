<?php


namespace QCloudSDK\Image;


use QCloudSDK\Core\Exceptions\InvalidArgumentException;
use QCloudSDK\COS\API as CosAPI;
use Tightenco\Collect\Support\Arr;

class Processor extends API
{
    const CONFIG_STYLE_SEPARATOR = 'StyleSeparator';
    const CONFIG_PRIVATE = 'private';
    /**
     * @var string
     */
    protected $cdnUrl;
    /**
     * @var string
     */
    protected $region;
    /**
     * @var int
     */
    protected $private = 0;
    /**
     * @var boolean|string
     */
    protected $cdn = false;
    /**
     * @var string
     */
    protected $path = '';
    /**
     * @var string
     */
    protected $separator;
    /**
     * @var string
     */
    protected $style = '';

    protected function init()
    {
        $this->region = Arr::get($this->config, CosAPI::CONFIG_REGION);
        $this->private = Arr::get($this->config, static::CONFIG_PRIVATE, 0);
        $this->separator = Arr::get($this->config, static::CONFIG_STYLE_SEPARATOR, '/');
        parent::init();
    }

    protected function setApiUrl()
    {
        $scheme = Arr::get($this->config, static::CONFIG_API_SCHEME, static::DEFAULT_SCHEME);
        $this->apiUrl = sprintf('%s://%s-%s.pic%s.myqcloud.com', $scheme, $this->bucket, $this->appId, $this->region);
        $this->cdnUrl = sprintf('%s://%s-%s.image.myqcloud.com', $scheme, $this->bucket, $this->appId);
    }

    public function region(string $region)
    {
        $this->region = $region;
        $this->setApiUrl();
        return $this;
    }

    public function bucket($bucket)
    {
        return $this->setBucket($bucket);
    }

    public function separator(string $symbol)
    {
        $this->separator = $symbol;
        return $this;
    }

    public function private(int $ttl = 300)
    {
        if($ttl > 0) $this->private = $ttl;
        return $this;
    }

    public function public()
    {
        $this->private = 0;
        return $this;
    }

    public function reset()
    {
        $this->path = '';
        $this->style = '';
        $this->params = '';
        return $this;
    }

    public function file(string $path)
    {
        $this->path = trim($path);
        if($this->path[0] !== '/') $this->path = '/' . $this->path;
        return $this;
    }

    public function query(string $query)
    {
        $this->params = $query;
        return $this;
    }

    public function chain(ProcessingChain $chain)
    {
        $this->params = $chain->queryString();
        return $this;
    }

    public function style(string $style)
    {
        $this->style = $style;
        return $this;
    }

    public function cdn()
    {
        $this->cdn = true;
        return $this;
    }

    public function direct()
    {
        $this->cdn = false;
        return $this;
    }

    public function domain(string $domain)
    {
        $this->cdn = $domain;
        if(substr($this->cdn, -1) === '/') $this->cdn = substr($this->cdn, 0, -1);
        return $this;
    }

    public function relativeUrl($signed = true)
    {
        if(empty($this->path) || $this->path === '/') throw new InvalidArgumentException('Path is empty');
        $url = $this->path;
        if($this->style) $url .= $this->separator . $this->style;
        if(!empty($this->params)) $url .= "?{$this->params}";
        if($signed && $this->private) $url .= (empty($this->params) ? '?' : '&') . 'sign=' . $this->signMultiEffect($this->private, $this->path);
        return $url;
    }

    public function absoluteUrl($signed = true)
    {
        return (is_bool($this->cdn) ? ($this->cdn ? $this->cdnUrl : $this->apiUrl) : $this->cdn) . $this->relativeUrl($signed);
    }

    protected function authorization()
    {
        if($this->private){
            $this->headers->offsetSet('Authorization', $this->signMultiEffect($this->private, $this->path));
        }else{
            $this->headers->forget('Authorization');
        }
        return $this;
    }

    public function download()
    {
        try{
            return $this->authorization()->getHttp()->request($this->absoluteUrl(), 'GET', ['headers' => $this->headers->all(), 'timeout' => 0]);
        }finally{
            $this->reset();
        }
    }

    protected function getJsonRequest()
    {
        try{
            return $this->authorization()->parseJSON('request', $this->absoluteUrl(), 'GET', ['headers' => $this->headers->all()]);
        }finally{
            $this->reset();
        }
    }

    /**
     * @link https://cloud.tencent.com/document/product/460/6926
     * @return \Tightenco\Collect\Support\Collection
     */
    public function exif()
    {
        return $this->query('exif')->getJsonRequest();
    }

    /**
     * @link https://cloud.tencent.com/document/product/460/6927
     * @return \Tightenco\Collect\Support\Collection
     */
    public function info()
    {
        return $this->query('imageInfo')->getJsonRequest();
    }

    /**
     * @link https://cloud.tencent.com/document/product/460/6928
     * @return \Tightenco\Collect\Support\Collection
     */
    public function ave()
    {
        return $this->query('imageAve')->getJsonRequest();
    }

    /**
     * It seems no use since COS methods do the things that need once signature
     * @param string $file
     * @param int|null $time
     * @param string|null $rand
     * @link https://cloud.tencent.com/document/product/460/6968
     * @return string
     */
    public function signOnce(string $file, int $time = null, string $rand = null)
    {
        return $this->signMultiEffect(0, $file, $time, $rand);
    }

}