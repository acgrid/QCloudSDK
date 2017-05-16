<?php


namespace QCloudSDK\Utils;


class Nonce
{
    public static function make()
    {
        return strval(random_int(10000, 100000));
    }
}