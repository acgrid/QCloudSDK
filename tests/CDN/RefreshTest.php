<?php

namespace QCloudSDKTests\CDN;

use QCloudSDK\CDN\Refresh;
use QCloudSDKTests\MockClient;
use QCloudSDKTests\TestCase;

class RefreshTest extends TestCase
{
    /**
     * @var Refresh
     */
    protected $refresh;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refresh = new Refresh(static::EXAMPLE_CONFIG, $this->http, $this->logger);
    }

    public function testRequest()
    {
        $this->http->setClient(MockClient::makeJson([Refresh::RESPONSE_CODE => Refresh::SUCCESS_CODE, Refresh::RESPONSE_MESSAGE => 'OK', 'data' => ['count' => 2, 'task_id' => 998]]));
        $this->assertSame(['count' => 2, 'task_id' => 998], $this->refresh->ensureRefreshUrls(['https://www.foo.com/a.html', 'https://www.foo.com/b/c.html'])->all());
        $this->http->setClient(MockClient::makeJson([Refresh::RESPONSE_CODE => Refresh::SUCCESS_CODE, Refresh::RESPONSE_MESSAGE => 'OK', 'data' => ['count' => 2, 'task_id' => 999]]));
        $this->assertSame(['count' => 2, 'task_id' => 999], $this->refresh->ensureRefreshDirs(['https://www.foo.com/a', 'https://www.foo.com/b/c/'])->all());
    }

    public function testQuery()
    {
        try{
            $this->refresh->queryByDate('2017-01-15', '2017-02-25');
            $this->fail('Should throw an exception if miss the expectation');
        }catch (\LogicException $e){
            $this->assertSame('Expected payload in path data.logs.', $e->getMessage());
        }

        $this->http = $this->getReflectedHttpWithResponse(json_encode([Refresh::RESPONSE_CODE => Refresh::SUCCESS_CODE, Refresh::RESPONSE_MESSAGE => 'OK', 'data' => ['logs' => $logs = [
            ['id' => 998, 'app_id' => 123456],
            ['id' => 999, 'app_id' => 123456],
        ]]]));
        $this->refresh->setHttp($this->http);
        $response1 = $this->refresh->queryById(998)->all();
        $response2 = $this->refresh->queryByUrl('http://www.example.org/')->all();
        $this->assertSame($response1, $response2);
        $this->assertSame($logs, $response2);
    }

}
