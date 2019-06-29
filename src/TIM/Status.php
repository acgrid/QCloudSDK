<?php


namespace QCloudSDK\TIM;


use QCloudSDK\Core\CustomDateParamTrait;
use QCloudSDK\Core\TimestampTrait;

class Status extends SmsAPI
{

    const TYPE_DELIVERY = 0;
    const TYPE_REPLY = 1;

    use TimestampTrait;
    use CustomDateParamTrait;

    /**
     * @var int
     */
    protected $type;

    public function queryDelivery()
    {
        $this->type = self::TYPE_DELIVERY;
        return $this;
    }

    public function queryReply()
    {
        $this->type = self::TYPE_REPLY;
        return $this;
    }

    public function getType()
    {
        if(!isset($this->type)) throw new \LogicException('Status to pull is not defined.');
        return $this->type;
    }

    /**
     * @link https://www.qcloud.com/document/product/382/5810
     * @param int $max
     * @return \Tightenco\Collect\Support\Collection
     */
    public function pullMultiStatus(int $max)
    {
        $type = $this->getType();
        $params = compact('type', 'max') + $this->signForGeneral($random);
        return $this->request('pullstatus', $random, $params);
    }

    /**
     * @link https://www.qcloud.com/document/product/382/5811
     * @param string $nationcode
     * @param string $mobile
     * @param $beginTime
     * @param $endTime
     * @param int $max
     * @return \Tightenco\Collect\Support\Collection
     */
    public function pullSingleStatus(string $nationcode, string $mobile, $beginTime, $endTime, int $max)
    {
        $type = $this->getType();
        $begin_time = $this->makeTimestampParam($beginTime);
        $end_time = $this->makeTimestampParam($endTime);
        $params = compact('type', 'max', 'begin_time', 'end_time', 'nationcode', 'mobile') + $this->signForGeneral($random);
        return $this->request('pullstatus4mobile', $random, $params);
    }

    /**
     * @param string $endpoint
     * @param int $begin_date
     * @param int $end_date
     * @return \Tightenco\Collect\Support\Collection
     */
    protected function requestStatus(string $endpoint, int $begin_date, int $end_date)
    {
        $params = compact('begin_date', 'end_date') + $this->signForGeneral($random);
        return $this->request($endpoint, $random, $params);
    }

    /**
     * @link https://www.qcloud.com/document/product/382/7755
     * @param $beginHour
     * @param $endHour
     * @return \Tightenco\Collect\Support\Collection
     */
    public function pullSendStatus($beginHour, $endHour)
    {
        return $this->requestStatus('pullsendstatus', intval($this->makeDateHourParam($beginHour)), intval($this->makeDateHourParam($endHour)));
    }

    /**
     * @link https://www.qcloud.com/document/product/382/7756
     * @param $beginHour
     * @param $endHour
     * @return \Tightenco\Collect\Support\Collection
     */
    public function pullCallbackStatus($beginHour, $endHour)
    {
        return $this->requestStatus('pullcallbackstatus', intval($this->makeDateHourParam($beginHour)), intval($this->makeDateHourParam($endHour)));
    }

}