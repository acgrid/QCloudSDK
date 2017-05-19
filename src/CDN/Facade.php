<?php


namespace QCloudSDK\CDN;

use QCloudSDK\Core\AbstractFacade;

/**
 * Class Facade
 * @package QCloudSDK\CDN
 * @property API $api
 * @property Refresh $refresh
 */
class Facade extends AbstractFacade
{
    protected $map = [
        'api' => API::class,
        'refresh' => Refresh::class,
    ];
}
