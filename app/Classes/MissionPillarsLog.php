<?php

namespace App\Classes;
use App\ErrorLog;
use App\MPLog;

/**
 * Description of Settings
 *
 * @author josemiguel
 */
class MissionPillarsLog {

    public static function exception($exception, $data = null, $event = 'exception') {
        \Log::info($exception);
        $data = [
            'event'=> $event,
            'exception_type'=> get_class($exception),
            'caller_function'=> self::callerFunction(),
            'exception_code' => $exception->getCode(),
            'error_message' => $exception->getMessage(),
            'file_name' => $exception->getFile(),
            'line_number' => $exception->getLine(),
            'requested_url' => request() ? request()->fullUrl() : null,
            'request_data' => request() ? request()->all() ? json_encode(request()->all()) : null : null,
            'request_headers' => request() ? json_encode(request()->header()) : null,
            'extra' => $data ? json_encode($data) : null,
            'created_by'=> request()->user() ? request()->user()->id : null,
            'created_by_session_id' => session()->getId()
        ];
        ErrorLog::create($data);
    }
    
    public static function click($link, $data = null, $event = 'click') {
        self::log([
            'event' => $event,
            'caller_function'=> self::callerFunction(),
            'message' => $link,
            'data' => $data
        ]);
    }
    
    public static function externalApiRequest($request, $message, $response, $event = 'external_api_request') {
        self::log([
            'event' => $event,
            'caller_function'=> self::callerFunction(),
            'message' => $message,
            'request' => $request,
            'response' => $response
        ]);
    }
    
    public static function log($data) {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7);
        array_shift($backtrace);
        
        $defaults = [
            'url'=> url()->current(),
            'caller_function'=> self::callerFunction(),
            'request'=> request() ? json_encode(request()->all()) : NULL,
        ];
        $data = array_merge($defaults, $data);
        MPLog::create($data);
    }
    
    public static function deprecated($data = []) {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7);
        // array_shift($backtrace);
        self::log( [
                'event' => 'Deprecated',
                'caller_function'=> self::callerFunction(),
                'data'=>json_encode([ 'backtrace'=>$backtrace ])
            ]
        );
    }
    
    protected static function callerFunction() {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
        array_shift($backtrace);
        array_shift($backtrace);
        $caller = array_shift($backtrace);
        
        $caller_function = $caller ? (
            in_array('class', $caller) ? caller['class'] . '::' : ''
            ) . $caller['function'] : NULL;
        return $caller_function;
    }
}
