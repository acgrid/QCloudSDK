<?php


namespace QCloudSDK\Core;

trait JsonParamTrait
{
    protected function ensureJsonParam(array &$param, string $key, callable $ensure = null)
    {
        if (isset($param[$key]) && !is_string($param[$key])) {
            if (isset($ensure)) {
                call_user_func_array($ensure, [&$param[$key]]);
            }
            $param[$key] = json_encode($param[$key]);
        }
    }
}
