<?php


namespace QCloudSDK\Core;


trait IntegerArrayTrait
{

    protected function makeIntegerArray($list)
    {
        return is_array($list) ? array_unique(array_map('intval', $list)) : intval($list);
    }

}