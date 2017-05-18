<?php

namespace QCloudSDKTests\Core;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use QCloudSDK\Core\Http;
use QCloudSDK\Facade\APIs;
use QCloudSDK\Facade\Config;
use QCloudSDK\Utils\Log;
use QCloudSDKTests\TestCase;

class FacadeTest extends TestCase
{

    public function testConfig()
    {
        $facade = new APIs([Config::COMMON_SECRET_ID => 'abcdefghijklmn', Config::COMMON_SECRET_KEY => 'foo']);
        $this->assertInstanceOf(Config::class, $facade['config']);
        $providers = $facade->getProviders();
        foreach ($providers as $provider) {
            $container = new Container();
            $container->register(new $provider());
            $container['config'] = $facade->raw('config');

            foreach ($container->keys() as $providerName) {
                $this->assertEquals($container->raw($providerName), $facade->raw($providerName));
            }

            unset($container);
        }
        $logger = Log::getLogger();
        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertSame('foo', $facade['config'][Config::COMMON_SECRET_KEY]);
    }

    public function testSetLogger()
    {
        $handler = new TestHandler();
        $logger = new Logger('Test');
        $logger->pushHandler($handler);
        Log::setLogger($logger);
        $this->assertSame($logger, Log::getLogger());
        new APIs(['debug' => true, Config::COMMON_SECRET_ID => 'abcdefghijklmn', Config::COMMON_SECRET_KEY => 'foo123456789', 'foo' => ['AppKey' => '87743144531']]);
        $handler->hasRecordThatPasses(function($record){
            $this->assertSame('Current config:', $record['message']);
            $this->assertSame(['debug' => true, Config::COMMON_SECRET_ID => '***jklmn', Config::COMMON_SECRET_KEY => '***56789', 'foo' => ['AppKey' => '***44531']], $record['context']);
        }, Logger::DEBUG);
    }

    public function testGuzzleOptions()
    {
        new APIs([]);
        $this->assertEquals(APIs::GUZZLE_DEFAULTS, Http::getDefaultOptions());

        $config = ['guzzle' => ['timeout' => 6]];
        new APIs($config);

        $this->assertEquals($config['guzzle'], Http::getDefaultOptions());
    }

    /**
     * test __set, __get.
     */
    public function testMagicMethod()
    {
        $app = new APIs([]);

        $app->cdn = 'destroyed';

        $this->assertEquals('destroyed', $app->cdn);
    }

    /**
     * Test addProvider() and setProviders.
     */
    public function testProviders()
    {
        $app = new APIs(['foo' => 'bar']);

        $providers = $app->getProviders();

        $app->addProvider(\Mockery::mock(ServiceProviderInterface::class));

        $this->assertCount(count($providers) + 1, $app->getProviders());

        $app->setProviders(['foo', 'bar']);

        $this->assertSame(['foo', 'bar'], $app->getProviders());
    }

}
