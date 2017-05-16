<?php


namespace QCloudSDK\TIM;


use QCloudSDK\Core\AbstractFacade;

/**
 * Class Facade
 * @package QCloudSDK\TIM
 * @property SMS $sms
 * @property Voice $voice
 * @property Template $template
 * @property Signature $signature
 * @property Status $status
 */
class Facade extends AbstractFacade
{

    protected $map = [
        'sms' => SMS::class,
        'voice' => Voice::class,
        'template' => Template::class,
        'signature' => Signature::class,
        'status' => Status::class,
    ];

}