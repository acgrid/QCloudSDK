<?php


namespace QCloudSDK\Facade;



use Tightenco\Collect\Support\Collection;

class Config extends Collection
{
    const COMMON_REGION = 'Region';
    const COMMON_SECRET_ID = 'SecretID';
    const COMMON_SECRET_KEY = 'SecretKey';
    const GUZZLE_DEFAULTS = 'guzzle';
    const COMMON_MAX_RETRIES = 'MaxRetries';
    const MODULE_COMMON_ENDPOINT = 'endpoint';
}