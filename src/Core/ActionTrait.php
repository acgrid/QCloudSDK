<?php


namespace QCloudSDK\Core;

trait ActionTrait
{
    abstract protected function createParam($key, $action);

    protected function createAction($action)
    {
        return $this->createParam('Action', ucfirst($action));
    }
}
