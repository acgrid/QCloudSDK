<?php


namespace QCloudSDK\Facade\Provider;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use QCloudSDK\WSS\API;

class WSS implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['wss'] = function (Container $container){
            return new API($container['config']);
        };
    }

}