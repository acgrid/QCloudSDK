<?php


namespace QCloudSDK\Facade\Provider;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use QCloudSDK\TIM\Facade;

class TIM implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['tim'] = function (Container $container){
            return new Facade($container);
        };
    }

}