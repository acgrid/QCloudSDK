<?php


namespace QCloudSDKTests;


use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class PhpHandler
{
    /**
     * @var callable
     */
    private $callback;
    private $lastRequest;
    private $lastOptions;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke(RequestInterface $request, array $options)
    {

        if (isset($options['delay'])) {
            usleep($options['delay'] * 1000);
        }

        $this->lastRequest = $request;
        $this->lastOptions = $options;

        $response = call_user_func($this->callback, $request, $options);

        $response = $response instanceof \Exception
            ? new RejectedPromise($response)
            : \GuzzleHttp\Promise\promise_for($response);

        return $response->then(
            function (ResponseInterface $value) use ($request, $options) {
                $this->invokeStats($request, $options, $value);

                if (isset($options['sink'])) {
                    $contents = (string) $value->getBody();
                    $sink = $options['sink'];

                    if (is_resource($sink)) {
                        fwrite($sink, $contents);
                    } elseif (is_string($sink)) {
                        file_put_contents($sink, $contents);
                    } elseif ($sink instanceof StreamInterface) {
                        $sink->write($contents);
                    }
                }

                return $value;
            },
            function ($reason) use ($request, $options) {
                $this->invokeStats($request, $options, null, $reason);
                return new RejectedPromise($reason);
            }
        );
    }

    /**
     * Get the last received request.
     *
     * @return RequestInterface
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * Get the last received request options.
     *
     * @return RequestInterface
     */
    public function getLastOptions()
    {
        return $this->lastOptions;
    }

    private function invokeStats(
        RequestInterface $request,
        array $options,
        ResponseInterface $response = null,
        $reason = null
    ) {
        if (isset($options['on_stats'])) {
            $stats = new TransferStats($request, $response, 0, $reason);
            call_user_func($options['on_stats'], $stats);
        }
    }

}