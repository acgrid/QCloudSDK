<?php


namespace QCloudSDK\Core;


trait DateTimeTrait
{
    protected function makeDateTimeParam($value, $format = 'Y-m-d H:i:s')
    {
        if($value instanceof \DateTimeInterface) return $value->format($format);
        if(is_numeric($value)) return date($format, $value);
        if(is_string($value) && $ts = strtotime($value)) return date($format, $ts);
        throw new \InvalidArgumentException('Unsupported datetime value.');
    }

    protected function makeDateParam($value)
    {
        return $this->makeDateTimeParam($value, 'Y-m-d');
    }

}