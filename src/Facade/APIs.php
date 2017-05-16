<?php


namespace QCloudSDK\Facade;


use Pimple\Container;
use QCloudSDK\Core\Http;
use QCloudSDK\Utils\Log;

/**
 * Class APIs
 * @package QCloudSDK\Facade
 * @property \QCloudSDK\CDN\Facade $cdn
 * @property \QCloudSDK\TIM\Facade $tim
 * @property \QCloudSDK\WSS\API    $wss
 */
class APIs extends Container
{
    const GUZZLE_DEFAULTS = ['timeout' => 5.0];

    protected $providers = [
        Provider\CDN::class,
        Provider\TIM::class,
        Provider\WSS::class,
        Provider\COS::class,
    ];

    public function __construct(array $config)
    {
        parent::__construct();
        $this['config'] = $config = new Config($config);
        $this->registerProviders();
        Http::setDefaultOptions($config->get(Config::GUZZLE_DEFAULTS, static::GUZZLE_DEFAULTS));
        if($config->get('debug', false)){
            $masked = clone $config;
            foreach ([Config::COMMON_SECRET_ID, Config::COMMON_SECRET_KEY] as $key) {
                !$masked->has($key) || $masked[$key] = '***'.substr($masked[$key], -5);
            }
            Log::debug('Current config:', $masked->toArray());
        }
    }

    /**
     * Add a provider.
     *
     * @param string $provider
     *
     * @return $this
     */
    public function addProvider($provider)
    {
        array_push($this->providers, $provider);

        return $this;
    }

    /**
     * Set providers.
     *
     * @param array $providers
     */
    public function setProviders(array $providers)
    {
        $this->providers = [];

        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    /**
     * Return all providers.
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Magic get access.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Magic set access.
     *
     * @param string $id
     * @param mixed  $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * Register providers.
     */
    private function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

}