<?php


namespace QCloudSDK\Core;


trait NonceTrait
{
    protected function makeNonce()
    {
        try{
            $nonce = random_int(10000, 99999);
        }catch (\Exception $e){
            $nonce = mt_rand(10000, 99999);
        }
        return strval($nonce);
    }
}