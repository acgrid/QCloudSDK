<?php


namespace QCloudSDK\TIM;



use QCloudSDK\Core\IntegerArrayTrait;

class Template extends ServiceAPI
{

    use IntegerArrayTrait;

    /**
     * @link https://www.qcloud.com/document/product/382/5817
     * @param int $type
     * @param string $title
     * @param string $text
     * @param string $remark
     * @return \QCloudSDK\Utils\Collection
     */
    protected function add(int $type, string $title, string $text, string $remark)
    {
        $params = compact('type', 'title', 'text', 'remark') + $this->signForGeneral($random);
        return $this->request('add_template', $random, $params);
    }

    public function addNormal(string $title, string $text, string $remark)
    {
        return $this->add(SMS::TYPE_NORMAL, $title, $text, $remark);
    }

    public function addPromotion(string $title, string $text, string $remark)
    {
        return $this->add(SMS::TYPE_PROMOTION, $title, $text, $remark);
    }

    /**
     * @link https://www.qcloud.com/document/product/382/8649
     * @param int $tpl_id
     * @param int $type
     * @param string $title
     * @param string $text
     * @param string $remark
     * @return \QCloudSDK\Utils\Collection
     */
    public function edit(int $tpl_id, int $type, string $title, string $text, string $remark)
    {
        $params = compact('tpl_id', 'type', 'title', 'text', 'remark') + $this->signForGeneral($random);
        return $this->request('mod_template', $random, $params);
    }

    protected function makeTemplateIdList($idList)
    {
        return ['tpl_id' => $this->makeIntegerArray($idList)];
    }

    /**
     * @link https://www.qcloud.com/document/product/382/5818
     * @param $idList
     * @return \QCloudSDK\Utils\Collection
     */
    public function delete($idList)
    {
        $params = $this->makeTemplateIdList($idList) + $this->signForGeneral($random);
        return $this->request('del_template', $random, $params);
    }

    /**
     * @link https://www.qcloud.com/document/product/382/5819
     * @param $idList
     * @return \QCloudSDK\Utils\Collection
     */
    public function getSpecified($idList)
    {
        $params = $this->makeTemplateIdList($idList) + $this->signForGeneral($random);
        return $this->request('get_template', $random, $params);
    }

    /**
     * @link https://www.qcloud.com/document/product/382/5819
     * @param int $offset
     * @param int $max
     * @return \QCloudSDK\Utils\Collection
     */
    public function getPaged(int $offset = 0, int $max = 50)
    {
        $params = ['tpl_page' => compact('offset', 'max')] + $this->signForGeneral($random);
        return $this->request('get_template', $random, $params);
    }
    
}