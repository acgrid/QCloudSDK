<?php


namespace QCloudSDK\COS;


use QCloudSDK\Core\Exceptions\InvalidArgumentException;
use QCloudSDK\Core\FormDataTrait;

class File extends API
{
    const OVERWRITE = 0;
    const NO_OVERWRITE = 1;

    const UPLOAD_MAX_SIZE = 20971520;

    const SLICE_512KB = 524288;
    const SLICE_1MB = 1048576;
    const SLICE_2MB = 2097152;
    const SLICE_3MB = 3145728;

    use FormDataTrait;

    /**
     * @link https://www.qcloud.com/document/product/436/6066
     * @param string $path
     * @param string $filecontent
     * @param string|null $biz_attr
     * @param int|null $insertOnly
     * @return \QCloudSDK\Utils\Collection
     * @throws InvalidArgumentException
     */
    public function uploadString(string $path, string $filecontent, string $biz_attr = null, int $insertOnly = null)
    {
        if(strlen($filecontent) >= static::UPLOAD_MAX_SIZE) throw new InvalidArgumentException('Data is too large to upload directly. Use chunk-style uploading.');
        $op = 'upload';
        $sha1 = sha1($filecontent);
        return $this->targetSigned($path)->setParams($this->makeFormDataFromArray(compact('op', 'filecontent', 'sha1', 'biz_attr', 'insertOnly')))->postFormDataRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6066
     * @param string $path
     * @param string $localFile
     * @param string|null $biz_attr
     * @param int|null $insertOnly
     * @return \QCloudSDK\Utils\Collection
     * @throws InvalidArgumentException
     */
    public function uploadFile(string $path, string $localFile, string $biz_attr = null, int $insertOnly = null)
    {
        if(!is_file($localFile) || !is_readable($localFile)) throw new InvalidArgumentException("'$localFile' does not exist or can not be read.");
        if(filesize($localFile) >= static::UPLOAD_MAX_SIZE) throw new InvalidArgumentException('Data is too large to upload directly. Use chunk-style uploading.');
        $op = 'upload';
        $sha1 = sha1_file($localFile);
        return $this->targetSigned($path)->setParams($this->makeFormDataFromArray(compact('op', 'sha1', 'biz_attr', 'insertOnly')) + ['name' => 'filecontent', 'filename' => $localFile])->postFormDataRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6067
     * @param string $path
     * @param int $totalSize
     * @param int $sliceSize
     * @param string|null $biz_attr
     * @param int|null $insertOnly
     * @return \QCloudSDK\Utils\Collection
     */
    public function uploadSliceInit(
        string $path,
        int $totalSize,
        int $sliceSize,
        string $biz_attr = null,
        int $insertOnly = null
    ) {
        return $this->targetSigned($path)->setParams($this->makeFormDataFromArray(['filesize' => $totalSize, 'slice_size' => $sliceSize] + compact('biz_attr', 'insertOnly')))->postFormDataRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6068
     * @param string $path
     * @param string $filecontent
     * @param string $session
     * @param int $offset
     * @return \QCloudSDK\Utils\Collection
     */
    public function uploadSliceData(string $path, string $filecontent, string $session, int $offset)
    {
        $op = 'upload_slice_data';
        return $this->targetSigned($path)->setParams($this->makeFormDataFromArray(compact('op', 'filecontent', 'session', 'offset')))->postFormDataRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6074
     * @param string $path
     * @param string $session
     * @param int $filesize
     * @return \QCloudSDK\Utils\Collection
     */
    public function uploadSliceFinish(string $path, string $session, int $filesize)
    {
        $op = 'upload_slice_finish';
        return $this->targetSigned($path)->setParams($this->makeFormDataFromArray(compact('op', 'session', 'filesize')))->postFormDataRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6070
     * @param string $path
     * @return \QCloudSDK\Utils\Collection
     */
    public function uploadSliceList(string $path)
    {
        return $this->targetSigned($path)->setParams($this->makeFormDataFromArray(['op' => 'upload_slice_list']))->postFormDataRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6730
     * @param string $srcPath
     * @param string $destPath
     * @param int|null $overwrite
     * @return \QCloudSDK\Utils\Collection
     */
    public function move(string $srcPath, string $destPath, int $overwrite = null)
    {
        return $this->targetOnceSigned($srcPath)->setParams($this->makeFormDataFromArray(['op' => 'move', 'dest_fileid' => $destPath, 'to_over_write' => $overwrite]))->postFormDataRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/7419
     * @param string $srcPath
     * @param string $destPath
     * @param int|null $overwrite
     * @return \QCloudSDK\Utils\Collection
     */
    public function copy(string $srcPath, string $destPath, int $overwrite = null)
    {
        return $this->targetOnceSigned($srcPath)->setParams($this->makeFormDataFromArray(['op' => 'copy', 'dest_fileid' => $destPath, 'to_over_write' => $overwrite]))->postFormDataRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6069
     * @param string $path
     * @return \QCloudSDK\Utils\Collection
     */
    public function stat(string $path)
    {
        return $this->targetSigned($path)->setOp('stat')->getQueryRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6072
     * @param string $path
     * @param array $params
     * @return \QCloudSDK\Utils\Collection
     */
    public function update(string $path, array $params)
    {
        $params['op'] = 'update';
        return $this->targetOnceSigned($path)->setParams($params)->postJsonRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6073
     * @param string $path
     * @return \QCloudSDK\Utils\Collection
     */
    public function delete(string $path)
    {
        return $this->targetOnceSigned($path)->setOp('delete')->postJsonRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/8429
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getObject()
    {
        $this->headers['Host'] = sprintf('%s-%s.%s.mycloud.com', $this->bucket, $this->appId, $this->appRegion);
        return $this->getHttp()->request("/$this->path", 'GET', ['headers' => $this->headers]);
    }

    public function downloadPublic($path)
    {
        return $this->target($path)->getObject();
    }

    public function downloadPrivate($path)
    {
        return $this->targetSigned($path)->getObject();
    }
    
}