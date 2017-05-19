<?php


namespace QCloudSDK\Facade\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use QCloudSDK\COS\Facade;

class COS implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['cos'] = function (Container $container) {
            return new Facade($container);
        };
    }
}
