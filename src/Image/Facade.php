<?php


namespace QCloudSDK\Image;


use QCloudSDK\Core\AbstractFacade;

/**
 * Class Facade
 * @package QCloudSDK\Image
 * @property Processor $processor
 * @property Face $face
 */
class Facade extends AbstractFacade
{
    protected $map = [
        'processor' => Processor::class,
        'face' => Face::class,
    ];
}