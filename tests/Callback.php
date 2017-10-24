<?php


namespace QCloudSDKTests;


use QCloudSDK\Core\AbstractCallback;
use QCloudSDK\Core\JSONCallback;

class Callback extends AbstractCallback
{
    const SAMPLE_KEY = 'secret';
    public $action;

    use JSONCallback;

    protected function checkAuthentic()
    {
        return isset($this->decoded->key) && $this->decoded->key === self::SAMPLE_KEY;
    }

    protected function dispatch()
    {
        $this->action = $this->decoded->action ?? null;
        switch($this->action){
            case 'A':
            case 'B':
            case 'C': $this->trigger($this->action, (array) $this->decoded); return true;
            default: return null;
        }
    }

    public function onA($handler)
    {
        $this->on('A', $handler);
    }

}