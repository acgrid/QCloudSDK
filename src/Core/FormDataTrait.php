<?php


namespace QCloudSDK\Core;

trait FormDataTrait
{
    protected function makeFormDataFromArray(array $data)
    {
        $data = array_filter($data, function ($value) {
            return $value !== null;
        });
        return array_map(function ($name, $contents) {
            return compact('name', 'contents');
        }, array_keys($data), array_values($data));
    }
}
