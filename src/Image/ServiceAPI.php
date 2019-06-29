<?php


namespace QCloudSDK\Image;


use Tightenco\Collect\Support\Arr;

class ServiceAPI extends API
{
    protected $bucketInURL = false;

    use ImageTrait;

    protected function reset()
    {
        unset($this->paramType);
        $this->params = [];
    }

    protected function addParams(array $params)
    {
        if($this->paramType === 'multipart'){
            $this->params = array_merge($this->params, $this->makeFormDataFromArray($params));
        }else{
            $this->params += $params;
        }
    }

    protected function setApiUrl()
    {
        $this->apiUrl = sprintf('%s://service.%s/', Arr::get($this->config, static::CONFIG_API_SCHEME, 'https'), Arr::get($this->config, static::CONFIG_API_HOST, 'image.myqcloud.com'));
    }

    protected function request(string $method, string $endpoint, string $paramOption)
    {
        $this->addParams(['appid' => $this->appId]);
        $this->headers->offsetSet('Authorization', $this->signMultiEffect());
        return $this->parseJSON('request', $this->apiUrl . $endpoint, $method, [$paramOption => $this->params, 'headers' => $this->headers->all(), 'timeout' => 60]);
    }

    public function postRequest(string $endpoint)
    {
        return $this->request('POST', $endpoint, $this->paramType);
    }

}