<?php


namespace QCloudSDK\TIM;


class SMS extends API
{
    const SMS_ENDPOINT = 'SmsEndpoint';

    const TYPE_NORMAL = 0;
    const TYPE_PROMOTION = 1;

    const DOMESTIC_CODE = '86';
    const MAX_MULTI = 200;

    /**
     * @var string
     */
    protected $sign;
    /**
     * @var int
     */
    protected $template;
    /**
     * @var array
     */
    protected $templateVariables;
    /**
     * @var int
     */
    protected $literalType;
    /**
     * @var string
     */
    protected $literalMessage;
    /**
     * @var string
     */
    protected $extend;
    /**
     * @var string
     */
    protected $ext;

    protected function init()
    {
        parent::init();
        $this->endpoint = $this->endpoint . $this->getLocalConfig(self::SMS_ENDPOINT, 'v5/tlssmssvr/');
    }

    public function setSign($sign)
    {
        $this->sign = $sign;
        return $this;
    }

    public function useTemplate(int $id, array $params)
    {
        $this->template = $id;
        $this->templateVariables = array_map('strval', $params);
        unset($this->literalType);
        unset($this->literalMessage);
        return $this;
    }

    protected function useLiteral(int $type, string $message)
    {
        unset($this->template);
        unset($this->templateVariables);
        $this->literalType = $type;
        $this->literalMessage = $message;
        return $this;
    }

    public function useNormal(string $message)
    {
        return $this->useLiteral(self::TYPE_NORMAL, $message);
    }

    public function usePromotion(string $message)
    {
        return $this->useLiteral(self::TYPE_PROMOTION, $message);
    }

    /**
     * @param string $extend
     * @return SMS
     */
    public function setExtend(string $extend): SMS
    {
        $this->extend = $extend;
        return $this;
    }

    /**
     * @param string $ext
     * @return SMS
     */
    public function setExt(string $ext): SMS
    {
        $this->ext = $ext;
        return $this;
    }

    protected function prepareContent()
    {
        $params = [];
        if(isset($this->sign)) $params['sign'] = $this->sign;
        if(isset($this->template)){
            $params['tpl_id'] = $this->template;
            $params['params'] = $this->templateVariables;
        }elseif(isset($this->literalType)){
            $params['type'] = $this->literalType;
            $params['msg'] = $this->literalMessage;
        }else{
            throw new \LogicException('Cannot send SMS for message is undefined.');
        }
        $params['extend'] = $this->extend ?? '';
        $params['ext'] = $this->ext ?? '';
        return $params;
    }

    protected function send($endpoint, $normalizedNumber)
    {
        $params = ['tel' => $normalizedNumber] + $this->prepareContent() + $this->signForMobile($normalizedNumber, $random);
        return $this->request($endpoint, $random, $params);
    }

    /**
     * @see https://www.qcloud.com/document/product/382/5806
     * @see https://www.qcloud.com/document/product/382/5977
     * @param array $domesticNumbers
     * @return \QCloudSDK\Utils\Collection
     */
    public function sendMulti(array $domesticNumbers)
    {
        if(empty($domesticNumbers)) return null;
        if(count($domesticNumbers) > static::MAX_MULTI) throw new \InvalidArgumentException('Reached the limit of receivers in once multiple sending.');
        return $this->send('sendmultisms2', array_map(function($domesticNumber){
            return $this->makeMobile(static::DOMESTIC_CODE, $domesticNumber);
        }, $domesticNumbers));
    }

    /**
     * @see https://www.qcloud.com/document/product/382/5808
     * @see https://www.qcloud.com/document/product/382/5976
     * @see https://www.qcloud.com/document/product/382/8716
     * @param string $nationCodeOrInternationalNumberOrDomesticNumber
     * @param string|null $localNumber
     * @return \QCloudSDK\Utils\Collection
     */
    public function sendTo(string $nationCodeOrInternationalNumberOrDomesticNumber, string $localNumber = null)
    {
        if(isset($localNumber)){
            return $this->send('sendsms', $this->makeMobile($nationCodeOrInternationalNumberOrDomesticNumber, $localNumber));
        }elseif($nationCodeOrInternationalNumberOrDomesticNumber[0] === '+'){
            return $this->send('sendisms', $nationCodeOrInternationalNumberOrDomesticNumber);
        }else{
            return $this->send('sendsms', $this->makeMobile(static::DOMESTIC_CODE, $nationCodeOrInternationalNumberOrDomesticNumber));
        }
    }

}