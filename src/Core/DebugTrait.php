<?php


namespace QCloudSDK\Core;


use Psr\Log\LoggerAwareTrait;

trait DebugTrait
{
    use LoggerAwareTrait;

    protected function debug($message, $context = [])
    {
        if($this->logger) $this->logger->debug($message, $context);
    }
}