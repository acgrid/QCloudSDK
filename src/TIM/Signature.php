<?php


namespace QCloudSDK\TIM;



use QCloudSDK\Core\IntegerArrayTrait;

class Signature extends ServiceAPI
{

    use IntegerArrayTrait;

    /**
     * @link https://www.qcloud.com/document/product/382/6038
     * @param string $text
     * @param string $remark
     * @return \QCloudSDK\Utils\Collection
     */
    public function add(string $text, string $remark)
    {
        $params = compact('text', 'remark') + $this->signForGeneral($random);
        return $this->request('add_sign', $random, $params);
    }

    /**
     * @link https://www.qcloud.com/document/product/382/8650
     * @param int $sign_id
     * @param string $text
     * @param string $remark
     * @return \QCloudSDK\Utils\Collection
     */
    public function edit(int $sign_id, string $text, string $remark)
    {
        $params = compact('sign_id', 'text', 'remark') + $this->signForGeneral($random);
        return $this->request('mod_sign', $random, $params);
    }

    protected function makeTemplateIdList($idList)
    {
        return ['tpl_id' => $this->makeIntegerArray($idList)];
    }

    /**
     * @link https://www.qcloud.com/document/product/382/6039
     * @param array|int $idList
     * @return \QCloudSDK\Utils\Collection
     */
    public function delete($idList)
    {
        $params = $this->makeTemplateIdList($idList) + $this->signForGeneral($random);
        return $this->request('del_sign', $random, $params);
    }

    /**
     * @link https://www.qcloud.com/document/product/382/6040
     * @param array|int $idList
     * @return \QCloudSDK\Utils\Collection
     */
    public function get($idList)
    {
        $params = $this->makeTemplateIdList($idList) + $this->signForGeneral($random);
        return $this->request('get_sign', $random, $params);
    }
    
}