<?php


namespace QCloudSDK\Image;



use QCloudSDK\Core\Exceptions\InvalidArgumentException;

class Face extends ServiceAPI
{
    const DETECT_ALL_FACES = 0;
    const DETECT_BIGGEST_FACE = 1;

    protected function doSingleRequest($method, $image, array $extra = [])
    {
        $this->params = $this->makeImage($image);
        if(!empty($extra)) $this->addParams($extra);
        return $this->postRequest("face/$method");
    }

    /**
     * @param $image
     * @param int $mode
     * @link https://cloud.tencent.com/document/product/460/7401
     * @return \Tightenco\Collect\Support\Collection
     */
    public function detect($image, int $mode = self::DETECT_BIGGEST_FACE)
    {
        $this->reset();
        return $this->doSingleRequest(__FUNCTION__, $image, compact('mode'));
    }

    /**
     * @param $image
     * @param int $mode
     * @link https://cloud.tencent.com/document/product/460/7400
     * @return \Tightenco\Collect\Support\Collection
     */
    public function shape($image, int $mode = self::DETECT_BIGGEST_FACE)
    {
        $this->reset();
        return $this->doSingleRequest(__FUNCTION__, $image, compact('mode'));
    }

    /**
     * @param $image
     * @param $person_id
     * @link https://cloud.tencent.com/document/product/460/8107
     * @return \Tightenco\Collect\Support\Collection
     */
    public function verify($image, $person_id)
    {
        $this->reset();
        $this->params = $this->makeImage($image);
        return $this->doSingleRequest(__FUNCTION__, $image, compact('person_id'));
    }

    /**
     * @param $imageA
     * @param $imageB
     * @link https://cloud.tencent.com/document/product/460/6897
     * @return \Tightenco\Collect\Support\Collection
     * @throws InvalidArgumentException
     */
    public function compare($imageA, $imageB)
    {
        $this->reset();
        $imageA = $this->makeImage($imageA, 'imageA', 'urlA');
        $paramType = $this->paramType;
        $imageB = $this->makeImage($imageB, 'imageB', 'urlB');
        $this->params = array_merge($imageA, $imageB);
        if($paramType !== $this->paramType) throw new InvalidArgumentException('Image and Url parameter can not be used at the same.');
        return $this->postRequest('face/compare');
    }

}