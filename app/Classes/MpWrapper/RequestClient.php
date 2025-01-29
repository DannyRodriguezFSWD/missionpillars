<?php

namespace App\Classes\MpWrapper;

use App\ExternalSystemRequestLog;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class RequestClient extends Client
{

    public function __construct(array $config = [])
    {
        $stack = HandlerStack::create();
        $stack->push(Middleware::tap(null,[$this,'logRequest']));
        $config['handler'] = $stack;
        return parent::__construct($config);
    }

    /**
     * @param Request $request
     * @param array $options
     * @param $response
     */
    public function logRequest(Request $request, $options, $response)
    {
        $request->getBody()->rewind();
        $requestBody = $request->getBody()->getContents();
        $requestHeaders = $request->getHeaders();
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $response = $response->wait();
        $log = [
            'category' => isset($backtrace[11]['class']) ? $backtrace[11]['class'] : null,
            'performing_function' => isset($backtrace[11]['function']) ? $backtrace[11]['function'] : null,
            'api_url' => $request->getUri(),
            'method' => $request->getMethod(),
            'request_data' => $requestBody ? $requestBody : null,
            'request_headers' => $requestHeaders ? json_encode($requestHeaders) : null,
            'response_data' => isset($response) ? $response->getBody()->getContents() : null,
            'response_headers' => isset($response) ? json_encode($response->getHeaders()) : null,
            'response_status_code' => isset($response) ? $response->getStatusCode() : null,
            'full_backtrace' => json_encode($backtrace)
        ];
        ExternalSystemRequestLog::create($log);
        $response->getBody()->rewind();
    }
}