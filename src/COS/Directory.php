<?php


namespace QCloudSDK\COS;


class Directory extends API
{
    /**
     * @link https://www.qcloud.com/document/product/436/6061
     * @param string $name
     * @param string|null $biz_attr
     * @return \QCloudSDK\Utils\Collection
     */
    public function create(string $name, string $biz_attr = null)
    {
        return $this->setOp('create')->targetSigned("$name/")->setParams(compact('biz_attr'))->postJsonRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6062
     * @param string $path
     * @param int $num
     * @param string|null $context
     * @return \QCloudSDK\Utils\Collection
     */
    public function ls(string $path = '', int $num = 1000, string $context = null)
    {
        return $this->setOp('list')->targetSigned($path)->setParams(compact('num', 'context'))->getQueryRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6063
     * @param string $directory
     * @return \QCloudSDK\Utils\Collection
     */
    public function stat(string $directory)
    {
        return $this->setOp('stat')->targetSigned("$directory/")->getQueryRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6064
     * @param string $directory
     * @return \QCloudSDK\Utils\Collection
     */
    public function delete(string $directory)
    {
        if(empty($directory) || $directory === '/') throw new \InvalidArgumentException("Cannot delete the root directory.");
        return $this->setOp('delete')->targetOnceSigned("$directory/")->postJsonRequest();
    }
}