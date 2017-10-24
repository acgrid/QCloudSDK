<?php


namespace QCloudSDK\Core;


use Psr\Http\Message\ServerRequestInterface;

trait JSONCallback
{
    protected $decoded;
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    protected function checkRequest()
    {
        $this->decoded = json_decode(strval($this->request->getBody()));
        return json_last_error() === JSON_ERROR_NONE;
    }

}