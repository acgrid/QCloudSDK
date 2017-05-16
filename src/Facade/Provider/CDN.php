<?php


namespace QCloudSDK\Facade\Provider;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use QCloudSDK\CDN\Facade;

class CDN implements ServiceProviderInterface
{

    public function register(Container $pimple)
    {
        $pimple['cdn'] = function (Container $container){
            return new Facade($container);
        };
    }
}