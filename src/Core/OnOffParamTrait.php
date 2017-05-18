<?php


namespace QCloudSDK\Core;


trait OnOffParamTrait
{
    protected function makeOnOffParam($value)
    {
        return $value || strtolower($value) === 'on' ? 'on' : 'off';
    }

    protected function ensureOnOffParam(array &$param, string $key)
    {
        if (isset($param[$key]) && $param[$key] !== 'on' && $param[$key] !== 'off') {
            $param[$key] = $this->makeOnOffParam($param[$key]);
        }
    }
}