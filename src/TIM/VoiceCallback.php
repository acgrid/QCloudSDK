<?php


namespace QCloudSDK\TIM;


use QCloudSDK\Core\AbstractCallback;
use QCloudSDK\Core\JSONCallback;

class VoiceCallback extends AbstractCallback
{
    use JSONCallback;

    const EVENT_CODE = 'code';
    const EVENT_PROMPT = 'prompt';
    const EVENT_KEY_PRESS = 'key';
    const EVENT_FAILURE = 'failure';

    /**
     * @param $handler
     * @link https://cloud.tencent.com/document/product/382/5814
     * @return $this
     */
    public function onCodeStatus($handler)
    {
        $this->on(self::EVENT_CODE, $handler);
        return $this;
    }

    /**
     * @param $handler
     * @link https://cloud.tencent.com/document/product/382/5816
     * @return $this
     */
    public function onPromptStatus($handler)
    {
        $this->on(self::EVENT_PROMPT, $handler);
        return $this;
    }

    /**
     * @param $handler
     * @link https://cloud.tencent.com/document/product/382/5815
     * @return $this
     */
    public function onKeyPress($handler)
    {
        $this->on(self::EVENT_KEY_PRESS, $handler);
        return $this;
    }

    /**
     * @param $handler
     * @link https://cloud.tencent.com/document/product/382/6532
     * @return $this
     */
    public function onFailure($handler)
    {
        $this->on(self::EVENT_FAILURE, $handler);
        return $this;
    }

    protected function tryTrigger(string $key, string $event)
    {
        if(isset($this->decoded->$key)) $this->trigger($event, $this->decoded->$key);
    }

    protected function dispatch()
    {
        $this->tryTrigger('voicecode_callback', self::EVENT_CODE);
        $this->tryTrigger('voiceprompt_callback', self::EVENT_PROMPT);
        $this->tryTrigger('voicekey_callback', self::EVENT_KEY_PRESS);
        $this->tryTrigger('voice_failure_callback', self::EVENT_FAILURE);
    }

}