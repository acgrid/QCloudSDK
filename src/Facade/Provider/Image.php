<?php


namespace QCloudSDK\Facade\Provider;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use QCloudSDK\Image\Facade;

class Image implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['image'] = function (Container $container){
            return new Facade($container);
        };
    }

}