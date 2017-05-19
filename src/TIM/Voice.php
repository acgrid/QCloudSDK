<?php


namespace QCloudSDK\TIM;

class Voice extends API
{
    const VOICE_ENDPOINT = 'VoiceEndpoint';

    const TEMPLATE_TYPE = 2; // WTF? Appeared in https://www.qcloud.com/document/product/382/8649

    /**
     * @var int
     */
    protected $playTimes;
    /**
     * @var string
     */
    protected $ext;

    protected function init()
    {
        parent::init();
        $this->endpoint = $this->endpoint . $this->getLocalConfig(self::VOICE_ENDPOINT, 'v5/tlsvoicesvr/');
    }

    /**
     * @param int $playTimes
     * @return Voice
     */
    public function setPlayTimes(int $playTimes): Voice
    {
        if ($playTimes > 0) {
            $this->playTimes = $playTimes;
        }
        return $this;
    }

    /**
     * @param string $ext
     * @return Voice
     */
    public function setExt(string $ext): Voice
    {
        $this->ext = $ext;
        return $this;
    }

    protected function prepareContent()
    {
        $params = [];
        if (isset($this->playTimes)) {
            $params['playtimes'] = $this->playTimes;
        }
        $params['ext'] = $this->ext ?? '';
        return $params;
    }

    /**
     * @link https://www.qcloud.com/document/product/382/5812
     * @param string $nationCode
     * @param string $mobile
     * @param string $verifyCode
     * @return \QCloudSDK\Utils\Collection
     */
    public function sendVerifyCode(string $nationCode, string $mobile, string $verifyCode)
    {
        $params = ['tel' => $this->makeMobile($nationCode, $mobile), 'msg' => $verifyCode] + $this->prepareContent() + $this->signForMobile($mobile, $random);
        return $this->request('sendvoice', $random, $params);
    }

    /**
     * @link https://www.qcloud.com/document/product/382/5813
     * @param string $nationCode
     * @param string $mobile
     * @param string $promptfile
     * @param int $prompttype
     * @return \QCloudSDK\Utils\Collection
     */
    public function sendPrompt(string $nationCode, string $mobile, string $promptfile, int $prompttype = 2)
    {
        $params = ['tel' => $this->makeMobile($nationCode, $mobile)] + compact('promptfile', 'prompttype') + $this->prepareContent() + $this->signForMobile($mobile, $random);
        return $this->request('sendvoiceprompt', $random, $params);
    }
}
