<?php


namespace QCloudSDK\Core;


trait CustomDateParamTrait
{
    protected function makeDateHourParam($value, $format = 'YmdH')
    {
        if(is_string($value)){
            if(\DateTime::createFromFormat($format, $value)){
                return $value;
            }elseif($ts = strtotime($value)){
                return date($format, $ts);
            }
        }
        if($value instanceof \DateTimeInterface) return $value->format($format);
        if(is_numeric($value)) return date($format, $value);
        throw new \InvalidArgumentException('Unsupported datetime value.');
    }
}