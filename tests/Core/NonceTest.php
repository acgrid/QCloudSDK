<?php


namespace QCloudSDKTests\Core{

    use PHPUnit\Framework\TestCase;
    use QCloudSDK\Core\NonceTrait;

    class NonceTest extends TestCase
    {

        use NonceTrait;

        public function testNonce()
        {
            $this->assertSame('89999', $this->makeNonce());
        }

    }
}

namespace QCloudSDK\Core{
    function random_int()
    {
        throw new \RuntimeException();
    }

    function mt_rand($min, $max)
    {
        return $max - $min;
    }
}
