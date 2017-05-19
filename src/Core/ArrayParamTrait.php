<?php


namespace QCloudSDK\Core;

trait ArrayParamTrait
{
    protected function makeArrayParam(string $name, $params, callable $ensure = null)
    {
        if (is_string($params) || is_numeric($params)) {
            $params = [$params];
        } elseif (is_array($params)) {
            $params = array_values($params);
        } elseif ($params instanceof \Traversable) {
            $params = iterator_to_array($params, false);
        } else {
            throw new \InvalidArgumentException('Parameter must be either string of something traversable.');
        }
        if (isset($ensure)) {
            array_walk($params, $ensure);
        }
        return array_combine(array_map(function ($index) use ($name) {
            return "$name.$index";
        }, array_keys($params)), $params);
    }
}
