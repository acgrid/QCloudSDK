<?php


namespace QCloudSDK\COS;

use QCloudSDK\Core\AbstractFacade;

/**
 * Class Facade
 * @package QCloudSDK\COS
 * @property API $api
 * @property Directory $dir
 * @property File $file
 */
class Facade extends AbstractFacade
{
    protected $map = [
        'api' => API::class,
        'dir' => Directory::class,
        'file' => File::class,
    ];
}
