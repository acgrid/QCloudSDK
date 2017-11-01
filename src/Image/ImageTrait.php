<?php


namespace QCloudSDK\Image;


use Psr\Http\Message\StreamInterface;
use QCloudSDK\Core\Exceptions\InvalidArgumentException;
use QCloudSDK\Core\FormDataTrait;

trait ImageTrait
{
    /**
     * @var string
     */
    protected $paramType;

    use FormDataTrait;

    protected function makeImage($image, string $binaryKey = 'image', string $urlKey = 'url', string $urlParamType = 'json')
    {
        $this->paramType = 'multipart';
        if(!(is_resource($image) || $image instanceof StreamInterface)){
            if(!is_string($image)) $image = strval($image);
            if(empty($image)) throw new InvalidArgumentException('Image should be resource, StreamInterface or non-empty string.');
            if(strlen($image) < 2048){
                if(filter_var($image, FILTER_VALIDATE_URL)){
                    $this->paramType = $urlParamType;
                }elseif(is_file($image) && is_readable($image)){
                    $image = fopen($image, 'rb');
                }
            }
        }
        if($this->paramType === 'multipart'){
            return $this->makeFormDataFromArray([$binaryKey => $image]);
        }else{
            return [$urlKey => $image];
        }
    }

}