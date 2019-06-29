<?php


namespace QCloudSDK\COS;


use QCloudSDK\Core\Exceptions\InvalidArgumentException;
use QCloudSDK\Core\FormDataTrait;
use Tightenco\Collect\Support\Collection;

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

    protected function makeBooleanInt($value)
    {
        return isset($value) ? ($value ? 1 : 0) : null;
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6066
     * @param string $path
     * @param string $filecontent
     * @param string|null $biz_attr
     * @param boolean $insertOnly
     * @return Collection
     * @throws InvalidArgumentException
     */
    public function uploadString(string $path, string $filecontent, string $biz_attr = null, $insertOnly = null)
    {
        if(strlen($filecontent) >= static::UPLOAD_MAX_SIZE) throw new InvalidArgumentException('Data is too large to upload directly. Use chunk-style uploading.');
        $op = 'upload';
        $sha1 = sha1($filecontent);
        $insertOnly = $this->makeBooleanInt($insertOnly);
        return $this->targetSigned($path)->setParams($this->makeFormDataFromArray(compact('op', 'filecontent', 'sha1', 'biz_attr', 'insertOnly')))->postFormDataRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6066
     * @param string $path
     * @param string $localFile
     * @param string|null $biz_attr
     * @param boolean $insertOnly
     * @return Collection
     * @throws InvalidArgumentException
     */
    public function uploadFile(string $path, string $localFile, string $biz_attr = null, $insertOnly = null)
    {
        if(!is_file($localFile) || !is_readable($localFile)) throw new InvalidArgumentException("'$localFile' does not exist or can not be read.");
        if(filesize($localFile) >= static::UPLOAD_MAX_SIZE) throw new InvalidArgumentException('Data is too large to upload directly. Use chunk-style uploading.');
        $op = 'upload';
        $sha1 = sha1_file($localFile);
        $insertOnly = $this->makeBooleanInt($insertOnly);
        $params = $this->makeFormDataFromArray(compact('op', 'sha1', 'biz_attr', 'insertOnly'));
        $params[] = ['name' => 'filecontent', 'contents' => fopen($localFile, 'rb'), 'filename' => $localFile];
        return $this->targetSigned($path)->setParams($params)->postFormDataRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6067
     * @param string $path
     * @param int $totalSize
     * @param int $sliceSize
     * @param string|null $biz_attr
     * @param boolean $insertOnly
     * @return Collection
     */
    public function uploadSliceInit(
        string $path,
        int $totalSize,
        int $sliceSize,
        string $biz_attr = null,
        $insertOnly = null
    ) {
        $insertOnly = $this->makeBooleanInt($insertOnly);
        return $this->targetSigned($path)->setParams($this->makeFormDataFromArray(['op' => 'upload_slice_init', 'filesize' => $totalSize, 'slice_size' => $sliceSize] + compact('biz_attr', 'insertOnly')))->postFormDataRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6068
     * @param string $path
     * @param string $filecontent
     * @param string $session
     * @param int $offset
     * @return \Tightenco\Collect\Support\Collection
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
     * @return \Tightenco\Collect\Support\Collection
     */
    public function uploadSliceFinish(string $path, string $session, int $filesize)
    {
        $op = 'upload_slice_finish';
        return $this->targetSigned($path)->setParams($this->makeFormDataFromArray(compact('op', 'session', 'filesize')))->postFormDataRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6070
     * @param string $path
     * @return \Tightenco\Collect\Support\Collection
     */
    public function uploadSliceList(string $path)
    {
        return $this->targetSigned($path)->setParams($this->makeFormDataFromArray(['op' => 'upload_slice_list']))->postFormDataRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6730
     * @param string $srcPath
     * @param string $destPath
     * @param boolean $overwrite
     * @return \Tightenco\Collect\Support\Collection
     */
    public function move(string $srcPath, string $destPath, $overwrite = null)
    {
        $overwrite = $this->makeBooleanInt($overwrite);
        return $this->targetOnceSigned($srcPath)->setParams($this->makeFormDataFromArray(['op' => 'move', 'dest_fileid' => $destPath, 'to_over_write' => $overwrite]))->postFormDataRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/7419
     * @param string $srcPath
     * @param string $destPath
     * @param boolean $overwrite
     * @return \Tightenco\Collect\Support\Collection
     */
    public function copy(string $srcPath, string $destPath, $overwrite = null)
    {
        $overwrite = $this->makeBooleanInt($overwrite);
        return $this->targetOnceSigned($srcPath)->setParams($this->makeFormDataFromArray(['op' => 'copy', 'dest_fileid' => $destPath, 'to_over_write' => $overwrite]))->postFormDataRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6069
     * @param string $path
     * @return \Tightenco\Collect\Support\Collection
     */
    public function stat(string $path)
    {
        return $this->targetSigned($path)->setOp('stat')->getQueryRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6072
     * @param string $path
     * @param array $params
     * @return \Tightenco\Collect\Support\Collection
     */
    public function update(string $path, array $params)
    {
        $params['op'] = 'update';
        return $this->targetOnceSigned($path)->setParams($params)->postJsonRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/6073
     * @param string $path
     * @return \Tightenco\Collect\Support\Collection
     */
    public function delete(string $path)
    {
        return $this->targetOnceSigned($path)->setOp('delete')->postJsonRequest();
    }

    /**
     * @link https://www.qcloud.com/document/product/436/8429
     * @param bool $cdn
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getObject(bool $cdn)
    {
        return $this->getHttp()->request($this->buildDownloadUrl($cdn), 'GET', ['headers' => $this->headers->forget('Host')->all(), 'timeout' => 0]);
    }

    public function downloadPublic($path, bool $cdn = false)
    {
        return $this->target($path)->getObject($cdn);
    }

    public function downloadPrivate($path, bool $cdn = false)
    {
        return $this->targetSigned($path)->getObject($cdn);
    }
    
}