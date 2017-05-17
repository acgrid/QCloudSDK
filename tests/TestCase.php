<?php


namespace QCloudSDKTests;


use QCloudSDK\Core\Http;

class TestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * @return Http
     */
    public function getReflectedHttp()
    {
        $http = new Http();
        $http->setClient(new ReflectClient());
        return $http;
    }

    protected function assertRequest(Http $http, \Closure $assertion)
    {
        call_user_func([$http->getClient(), 'assertRequest'], $assertion);
    }

    /**
     * Tear down the test case.
     */
    public function tearDown()
    {
        $this->finish();
        parent::tearDown();
        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->Mockery_getExpectationCount());
        }
        \Mockery::close();
    }

    /**
     * Run extra tear down code.
     */
    protected function finish()
    {
        // call more tear down methods
    }
}