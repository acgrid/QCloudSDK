<?php


namespace QCloudSDKTests;


use QCloudSDK\Core\AbstractCallback;

class Callback extends AbstractCallback
{
    const SAMPLE_KEY = 'secret';
    protected $decoded;
    public $action;

    protected function checkRequest()
    {
        $this->decoded = json_decode(strval($this->request->getBody()), true);
        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function checkAuthentic()
    {
        return isset($this->decoded['key']) && $this->decoded['key'] === self::SAMPLE_KEY;
    }

    protected function dispatch()
    {
        $this->action = $this->decoded['action'] ?? null;
        switch($this->action){
            case 'A':
            case 'B':
            case 'C': $this->trigger($this->action, $this->decoded); return true;
            default: return null;
        }
    }

    public function onA($handler)
    {
        $this->on('A', $handler);
    }

}