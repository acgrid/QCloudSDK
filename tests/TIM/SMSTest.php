<?php

namespace QCloudSDKTests\TIM;

use GuzzleHttp\Psr7\Uri;
use QCloudSDK\TIM\SMS;
use QCloudSDKTests\TestCase;

class SMSTest extends TestCase
{
    /**
     * @var SMS
     */
    protected $sms;

    protected function setUp()
    {
        parent::setUp();
        $this->sms = new SMS($this->configForTest(), $this->http);
    }

    public function testSig()
    {
        $strMobile = '13788888888';
        $random = '7226249334';
        $time = time();
        $common = "appkey=dffdfd6029698a5fdf4&random=$random&time=$time";
        $this->assertSame(hash("sha256", "$common&mobile=$strMobile"), $this->sms->signForMobile($strMobile, $random)['sig']);
        $this->assertSame(hash("sha256", "$common&mobile=13712345678,13987654321"), $this->sms->signForMobile([['mobile' => '13712345678'], ['mobile' => '13987654321']], $random)['sig']);
        $this->assertSame(hash("sha256", $common), $this->sms->signForGeneral($random)['sig']);

        $params = $this->sms->signForMobile('13012345789', $altRandom);
        $this->assertRegExp('/\d{5}/', $altRandom);
        $this->assertArrayHasKey('time', $params);
        $this->assertEquals(time(), $params['time'], '', 1);
    }

    public function testSendSingle()
    {
        $this->setUp();
        try{
            $this->sms->sendTo('13612345678');
            $this->fail('Should prevent sending if lacking information.');
        }catch (\LogicException $e){}

        $this->sms->useNormal('A sms packet.')->setExtend('WTF')->setExt('poi')->sendTo('13912345678');
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertContains('sendsms', $uri->getPath());
            parse_str($uri->getQuery(), $query);
            $this->assertArrayHasKey('sdkappid', $query);
            $this->assertArrayHasKey('random', $query);
        });
        $this->assertMyRequestJson(function($json){
            $this->assertSame(['nationcode' => '86', 'mobile' => '13912345678'], $json['tel']);
            $this->assertSame('A sms packet.', $json['msg']);
            $this->assertSame(SMS::TYPE_NORMAL, $json['type']);
            $this->assertSame('WTF', $json['extend']);
            $this->assertSame('poi', $json['ext']);
        });

        $this->sms->usePromotion('Advertisement')->sendTo('852', '98765432');
        $this->assertMyRequestJson(function($json){
            $this->assertSame(['nationcode' => '852', 'mobile' => '98765432'], $json['tel']);
            $this->assertSame('Advertisement', $json['msg']);
            $this->assertSame(SMS::TYPE_PROMOTION, $json['type']);
            $this->assertSame('WTF', $json['extend']);
            $this->assertSame('poi', $json['ext']);
        });

        $this->sms->setSign('SOS')->useTemplate(2048, ['验证码', 1334])->setExtend('VERITY')->sendTo('+8165243031');
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertContains('sendisms', $uri->getPath());
        });
        $this->assertMyRequestJson(function($json){
            $this->assertSame('+8165243031', $json['tel']);
            $this->assertArrayNotHasKey('type', $json);
            $this->assertArrayNotHasKey('msg', $json);
            $this->assertSame('SOS', $json['sign']);
            $this->assertSame(2048, $json['tpl_id']);
            $this->assertSame(['验证码', '1334'], $json['params']);
            $this->assertSame('VERITY', $json['extend']);
            $this->assertSame('poi', $json['ext']);
        });

    }

    public function testSendMulti()
    {
        $this->setUp();
        $this->assertNull($this->sms->sendMulti([]));
        try{
            $this->sms->sendMulti(array_fill(0, SMS::MAX_MULTI + 1, 'foo'));
            $this->fail('Should throw an exception on too many messages.');
        }catch (\InvalidArgumentException $e) {}
        $this->sms->setSign('SOS')->useTemplate(2048, ['验证码', '1334'])->setExtend('VERITY')->sendMulti(['13198765432', '13300001234']);
        $this->assertMyRequestUri(function(Uri $uri){
            $this->assertContains('sendmultisms2', $uri->getPath());
        });
        $this->assertMyRequestJson(function($json){
            $this->assertSame([
                ['nationcode' => '86', 'mobile' => '13198765432'],
                ['nationcode' => '86', 'mobile' => '13300001234'],
            ], $json['tel']);
            $this->assertArrayNotHasKey('type', $json);
            $this->assertArrayNotHasKey('msg', $json);
            $this->assertSame('SOS', $json['sign']);
            $this->assertSame(2048, $json['tpl_id']);
            $this->assertSame(['验证码', '1334'], $json['params']);
            $this->assertSame('VERITY', $json['extend']);
            $this->assertSame('', $json['ext']);
        });

    }

}
