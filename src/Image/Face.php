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

    public function detect($image, int $mode = self::DETECT_BIGGEST_FACE)
    {
        $this->reset();
        return $this->doSingleRequest(__FUNCTION__, $image, compact('mode'));
    }

    public function shape($image, int $mode = self::DETECT_BIGGEST_FACE)
    {
        $this->reset();
        return $this->doSingleRequest(__FUNCTION__, $image, compact('mode'));
    }

    public function verify($image, $person_id)
    {
        $this->reset();
        $this->params = $this->makeImage($image);
        return $this->doSingleRequest(__FUNCTION__, $image, compact('person_id'));
    }

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