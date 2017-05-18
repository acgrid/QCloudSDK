<?php

namespace QCloudSDKTests\TIM;

use GuzzleHttp\Psr7\Uri;
use QCloudSDK\TIM\SMS;
use QCloudSDK\TIM\Template;
use QCloudSDKTests\TestCase;

class TemplateTest extends TestCase
{
    /**
     * @var Template
     */
    protected $template;

    protected function setUp()
    {
        parent::setUp();
        $this->template = new Template($this->configForTest(), $this->http);
    }

    public function testAdd()
    {
        $this->template->addNormal('foo', 'bar', 'baz');
        $this->assertMyRequestUri(function (Uri $uri){
            $this->assertStringEndsWith('add_template', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) {
            $this->assertSame('foo', $json['title']);
            $this->assertSame('bar', $json['text']);
            $this->assertSame('baz', $json['remark']);
            $this->assertSame(SMS::TYPE_NORMAL, $json['type']);
        });

        $this->template->addPromotion('foo', 'bar', 'baz');
        $this->assertMyRequestJson(function ($json) {
            $this->assertSame(SMS::TYPE_PROMOTION, $json['type']);
        });
    }

    public function testEdit()
    {
        $this->template->mod(789, SMS::TYPE_PROMOTION, 'foo', 'bar', 'poi');
        $this->assertMyRequestUri(function (Uri $uri){
            $this->assertStringEndsWith('mod_template', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) {
            $this->assertSame(789, $json['tpl_id']);
            $this->assertSame('foo', $json['title']);
            $this->assertSame('bar', $json['text']);
            $this->assertSame('poi', $json['remark']);
            $this->assertSame(SMS::TYPE_PROMOTION, $json['type']);
        });
    }

    public function testDelete()
    {
        $this->template->delete([22, 33]);
        $this->assertMyRequestUri(function (Uri $uri){
            $this->assertStringEndsWith('del_template', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) {
            $this->assertSame([22, 33], $json['tpl_id']);
        });
    }

    public function testGet()
    {
        $this->template->getSpecified([7, 8, 9]);
        $this->assertMyRequestUri(function (Uri $uri){
            $this->assertStringEndsWith('get_template', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) {
            $this->assertSame([7, 8, 9], $json['tpl_id']);
            $this->assertArrayNotHasKey('tpl_page', $json);
        });

        $this->template->getPaged(20, 10);
        $this->assertMyRequestJson(function ($json) {
            $this->assertArrayNotHasKey('tpl_id', $json);
            $this->assertSame(20, $json['tpl_page']['offset']);
            $this->assertSame(10, $json['tpl_page']['max']);
        });
    }

}
