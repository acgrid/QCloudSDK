<?php


namespace QCloudSDK\TIM;


use QCloudSDK\Core\AbstractCallback;
use QCloudSDK\Core\JSONCallback;

class SMSCallback extends AbstractCallback
{
    use JSONCallback;

    const EVENT_REPLY = 'reply';
    const EVENT_REPORT = 'report';

    /**
     * @param $handler
     * @link https://cloud.tencent.com/document/product/382/5809
     * @return $this
     */
    public function onReply($handler)
    {
        $this->on(self::EVENT_REPLY, $handler);
        return $this;
    }

    /**
     * @param $handler
     * @link https://cloud.tencent.com/document/product/382/5807
     * @return $this
     */
    public function onReport($handler)
    {
        $this->on(self::EVENT_REPORT, $handler);
        return $this;
    }

    public function dispatch()
    {
        if(is_array($this->decoded)){
            foreach($this->decoded as $item){
                $this->trigger(self::EVENT_REPORT, $item);
            }
        }elseif(isset($this->decoded->nationcode, $this->decoded->mobile, $this->decoded->text, $this->decoded->time, $this->decoded->sign, $this->decoded->extend)){
            $this->trigger(self::EVENT_REPLY, $this->decoded->nationcode, $this->decoded->mobile, $this->decoded->text, $this->decoded->time, $this->decoded->sign, $this->decoded->extend);
        }
    }

}