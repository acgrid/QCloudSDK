<?php

namespace QCloudSDKTests\Core;


use QCloudSDK\Core\ActionTrait;
use QCloudSDK\Core\ArrayParamTrait;
use QCloudSDK\Core\CustomDateParamTrait;
use QCloudSDK\Core\DateTimeTrait;
use QCloudSDK\Core\FormDataTrait;
use QCloudSDK\Core\IntegerArrayTrait;
use QCloudSDK\Core\JsonParamTrait;
use QCloudSDK\Core\TimestampTrait;
use QCloudSDKTests\TestCase;

class ParamTraitsTest extends TestCase
{
    use ActionTrait;
    use ArrayParamTrait;
    use CustomDateParamTrait;
    use DateTimeTrait;
    use FormDataTrait;
    use IntegerArrayTrait;
    use JsonParamTrait;
    use TimestampTrait;


    public function testAction()
    {
        $this->assertSame(['Action' => 'TestAction'], $this->createAction(__FUNCTION__));
    }

    public function testArray()
    {
        $this->assertSame(['key.0' => 'value'], $this->makeArrayParam('key', 'value'));
        $this->assertSame(['key.0' => '1.6'], $this->makeArrayParam('key', 1.56, function(&$value){
            $value = strval(round($value, 1));
        }));
        $sample = ['key.0' => 'a', 'key.1' => 'b'];
        $this->assertSame($sample, $this->makeArrayParam('key', ['x' => 'a', 'y' => 'b']));
        $this->assertSame($sample, $this->makeArrayParam('key', (function(){
            foreach(['a', 'b'] as $value) yield $value;
        })()));
        try{
            $this->makeArrayParam('foo', new \stdClass());
            $this->fail('Should throw an exception for something can not be processed.');
        }catch (\InvalidArgumentException $e) {}
    }

    public function testDateTime()
    {
        $sample = new \DateTime();
        $this->assertSame($sample->format('Y-m-d H:i:s'), $this->makeDateTimeParam($sample));
        $this->assertSame($sample->format('Y-m-d H:i:s'), $this->makeDateTimeParam($sample->getTimestamp()));
        $this->assertSame($sample->format('Y-m-d H:i:s'), $this->makeDateTimeParam('now'));
        $this->assertSame($sample->format('Y-m-d'), $this->makeDateParam($sample));
        try{
            $this->makeDateParam([]);
            $this->fail('Should throw an exception on nothing can be a date.');
        }catch (\InvalidArgumentException $e) {}

        $this->assertSame($sample->format('YmdH'), $this->makeDateHourParam($sample));
        $this->assertSame($sample->format('YmdH'), $this->makeDateHourParam('now'));
        $this->assertSame($sample->format('YmdH'), $this->makeDateHourParam($sample->format('YmdH')));
        $this->assertSame($sample->format('YmdH'), $this->makeDateHourParam($sample->getTimestamp()));
        try{
            $this->makeDateHourParam([]);
            $this->fail('Should throw an exception on nothing can be a date.');
        }catch (\InvalidArgumentException $e) {}
    }

    public function testFormData()
    {
        $this->assertSame([
            ['name' => 'x', 'contents' => 'foo'],
            ['name' => 'z', 'contents' => 'bar'],
        ], $this->makeFormDataFromArray(['x' => 'foo', 'y' => null, 'z' => 'bar']));
    }

    public function testIntegerArray()
    {
        $this->assertSame(3, $this->makeIntegerArray('3.0'));
        $this->assertSame([10, 43, 653], $this->makeIntegerArray(['10', 43.0, 653, 10]));
    }

    public function testJson()
    {
        $params = ['foo' => ['x' => 'b', 'y' => 'a']];
        $this->ensureJsonParam($params, 'foo', function(&$value){
            unset($value['x']);
        });
        $this->assertSame(json_encode(['y' => 'a']), $params['foo']);
    }

    public function testTimestamp()
    {
        $time = time();
        $now = new \DateTime();
        $now->setTimestamp($time);
        $this->assertSame($time, $this->makeTimestampParam(strval($time)));
        $this->assertSame($time, $this->makeTimestampParam(date('Y-m-d H:i:s', $time)));
        $this->assertSame($time, $this->makeTimestampParam($now));
        try{
            $this->makeTimestampParam([]);
            $this->fail('Should throw an exception on nothing can be a date.');
        }catch (\InvalidArgumentException $e) {}
    }

}
