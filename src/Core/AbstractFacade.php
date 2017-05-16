<?php


namespace QCloudSDK\Core;


use Pimple\Container;

abstract class AbstractFacade
{
    /**
     * @var Container
     */
    private $container;
    /**
     * @var array
     */
    protected $map = [];
    /**
     * @var array
     */
    private $instances = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __get($name)
    {
        if(!isset($this->instances[$name])){
            if(!isset($this->map[$name])) throw new \RuntimeException('Undefined item key in facade.');
            $targetClass = $this->map[$name];
            $this->instances[$name] = new $targetClass($this->container['config']);
        }
        return $this->instances[$name];
    }
}