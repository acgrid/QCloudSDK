<?php


namespace QCloudSDK\Core;

trait TimestampTrait
{
    protected function makeTimestampParam($value)
    {
        if (is_numeric($value)) {
            return intval($value);
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }
        if (is_string($value) && $ts = strtotime($value)) {
            return $ts;
        }
        throw new \InvalidArgumentException('Unsupported datetime value.');
    }
}
