<?php


namespace QCloudSDK\TIM;


use QCloudSDK\Core\AbstractAPI;
use QCloudSDK\Core\NonceTrait;
use QCloudSDK\Facade\Config;

abstract class API extends AbstractAPI
{
    const CONFIG_SECTION = 'tim';

    protected $signNeedEndpoint = false;

    protected $signNeedMethod = false;

    protected $appId;

    protected $appKey;

    protected $endpoint;

    const RESPONSE_CODE = 'result';
    const RESPONSE_MESSAGE = 'errmsg';

    const APP_ID = 'AppId';
    const APP_KEY = 'AppKey';

    use NonceTrait;

    protected function init()
    {
        $this->appId = $this->getLocalConfig(static::APP_ID);
        $this->appKey = $this->getLocalConfig(static::APP_KEY);
        $this->endpoint = $this->getLocalConfig(Config::MODULE_COMMON_ENDPOINT, "yun.tim.qq.com/");
    }

    protected function request($endpoint, string $random, array $params)
    {
        return $this->parseJSON('json', $this->endpoint . $endpoint, $params, ['sdkappid' => $this->appId, 'random' => $random]);
    }

    public function signForMobile($mobiles, &$random, $mobileParamName = 'mobile', int $time = null)
    {
        if(is_array($mobiles)){
            if(isset($mobiles['mobile'])){
                $mobiles = $mobiles['mobile'];
            }else{
                $mobiles = join(',', array_map(function($item){
                    return $item['mobile'];
                }, $mobiles));
            }
        }
        $sig = hash("sha256", $this->commonSignature($random, $time). "&$mobileParamName=$mobiles"); // https://github.com/qcloudsms/qcloudsms/blob/master/demo/php/SmsTools.php#L12
        return compact('time', 'sig');
    }

    public function signForGeneral(&$random, int $time = null)
    {
        $sig = hash("sha256", $this->commonSignature($random, $time));
        return compact('time', 'sig');
    }

    protected function commonSignature(&$random, int &$time = null)
    {
        if(!isset($random)) $random = $this->makeNonce();
        if(!isset($time)) $time = time();
        return "appkey={$this->appKey}&random=$random&time=$time";
    }

    protected function makeMobile($nationcode, $mobile)
    {
        return compact('nationcode', 'mobile');
    }

}