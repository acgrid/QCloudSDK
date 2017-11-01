<?php


namespace QCloudSDK\Image;


use QCloudSDK\Core\AbstractFacade;

/**
 * Class Facade
 * @package QCloudSDK\Image
 * @property Face $face
 */
class Facade extends AbstractFacade
{
    protected $map = [
        'face' => Face::class,
    ];
}