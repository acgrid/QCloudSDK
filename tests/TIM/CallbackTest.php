<?php

namespace QCloudSDKTests\TIM;


use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use QCloudSDK\Core\AbstractCallback;
use QCloudSDK\TIM\SMSCallback;
use QCloudSDK\TIM\VoiceCallback;

class CallbackTest extends TestCase
{
    /**
     * @var SMSCallback
     */
    protected $sms;
    /**
     * @var VoiceCallback
     */
    protected $voice;

    protected $actual;

    protected $smsReply = ['nationcode' => '86', 'mobile' => '13xxxxxxxxx', 'text' => '用户回复的内容', 'sign' => '短信签名', 'time' => 1457336869, 'extend' => '扩展码'];
    protected $smsReport = [
        ['user_receive_time' => '2015-10-17 08:03:04', 'nationcode' => '86', 'mobile' => '13xxxxxxxxx', 'report_status' => 'SUCCESS', 'errmsg' => 'DELIVRD', 'description' => '用户短信送达成功', 'sid' => 'xxxxxxx'],
        ['user_receive_time' => '2015-10-17 08:04:04', 'nationcode' => '86', 'mobile' => '13yyyyyyyyy', 'report_status' => 'SUCCESS', 'errmsg' => 'DELIVRD', 'description' => '用户短信送达成功', 'sid' => 'yyyyyyy'],
    ];

    protected $voiceCode = ['result' => 0, 'callid' => 'xxxxxx', 'mobile' => '13xxxxxxxxx', 'nationcode' => '86', 'call_from' => '075583763333', 'start_calltime' => '1470196821', 'end_calltime' => '1470196843', 'accept_time' => '1470196835', 'fee' => '1'];
    protected $voicePrompt = ['result' => 0, 'callid' => 'xxxxxx', 'mobile' => '13yyyyyyyyy', 'nationcode' => '86', 'call_from' => '075583763333', 'start_calltime' => '1470196821', 'end_calltime' => '1470196843', 'accept_time' => '1470196835', 'fee' => '1'];
    protected $voiceKey = ['callid' => 'xxxxxx', 'mobile' => '13xxxxxxxxx', 'nationcode' => '86', 'call_from' => '075583763333', 'keypress' => '2'];
    protected $voiceFailure = ['callid' => 'xxxxxx', 'mobile' => '13xxxxxxxxx', 'nationcode' => '86', 'call_from' => '075583763333', 'failure_code' => 8, 'failure_reason' => '空号'];

    protected function expectResult(AbstractCallback $callback, ServerRequestInterface $request, $expected)
    {
        $callback->respond($request);
        $this->assertSame($expected, $this->actual instanceof \stdClass ? (array) $this->actual : $this->actual);
    }

    public function testSMS()
    {
        $this->expectResult($this->sms, new ServerRequest('POST', '/sms', [], json_encode($this->smsReply)), "[{$this->smsReply['time']}][{$this->smsReply['sign']}][{$this->smsReply['extend']}] +{$this->smsReply['nationcode']}{$this->smsReply['mobile']}: {$this->smsReply['text']}");
        $this->actual = '';
        $this->expectResult($this->sms, new ServerRequest('POST', '/sms', [], json_encode($this->smsReport)), 'xxxxxxxyyyyyyy');
    }

    public function testVoice()
    {
        $this->expectResult($this->voice, new ServerRequest('POST', '/voice', [], json_encode(['voicecode_callback' => $this->voiceCode])), $this->voiceCode);
        $this->expectResult($this->voice, new ServerRequest('POST', '/voice', [], json_encode(['voiceprompt_callback' => $this->voicePrompt])), $this->voicePrompt);
        $this->expectResult($this->voice, new ServerRequest('POST', '/voice', [], json_encode(['voicekey_callback' => $this->voiceKey])), $this->voiceKey);
        $this->expectResult($this->voice, new ServerRequest('POST', '/voice', [], json_encode(['voice_failure_callback' => $this->voiceFailure])), $this->voiceFailure);
    }

    protected function setUp(): void
    {
        $this->sms = new SMSCallback();
        $this->sms->onReply(function($nationCode, $mobile, $text, $time, $sign, $extend){
            $this->actual = "[$time][$sign][$extend] +$nationCode$mobile: $text";
        })->onReport(function($item){
            $this->assertObjectHasAttribute('sid', $item);
            $this->actual .= $item->sid;
        });
        $logActual = function($notify){
            $this->actual = $notify;
        };
        $this->voice = new VoiceCallback();
        $this->voice->onCodeStatus($logActual)->onPromptStatus($logActual)->onKeyPress($logActual)->onFailure($logActual);
    }

}
