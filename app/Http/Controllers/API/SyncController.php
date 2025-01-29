<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Classes\ApiJsonResponse;
use App\Models\Purpose;
use App\Models\Campaign;
use App\Constants;
use Marcelgwerder\ApiHandler\Facades\ApiHandler;
use App\Classes\API\Sync;
use App\Classes\MissionPillarsLog;

class SyncController extends ApiController
{
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Called using GET HTTP request
     * @param Request $request
     */
    public function getSynchronized(Request $request) {
        try {
            $items = Purpose::query();
            $result = ApiHandler::parseMultiple($items)->getResult();
            $response = new ApiJsonResponse(200, $result);
            return $response->toJson();
        } catch (\Illuminate\Database\QueryException $ex) {
            $debug = env('APP_DEBUG');
            $response = $debug ? new ApiJsonResponse(400, $ex) : new ApiJsonResponse(400);
        }
        return $response->toJson();
    }
    
    /**
     * Called using POST HTTP request
     * @param Request $request
     */
    public function setSynchronized(Request $request) {
        if ($request->header('content-type') === array_get(Constants::API, 'REQUEST_JSON_TYPE')) {
            try {
                $sync = new Sync();
                //identify what we want to sync
                switch (array_get($request, 'sync')){
                    case 'pages':
                        $resource = $sync->pages($request);
                        break;
                    case 'transactions':
                        $resource = $sync->transactions($request);
                        break;
                    case 'remove_tenant':
                        $resource = $sync->removeTenant($request);
                        break;
                    case 'get_unpaid_invoices':
                        $resource = $sync->getUnpaidInvoices($request);
                        break;
                    default :
                        $resource = null;
                        break;
                }
                
                $response = new ApiJsonResponse(200, $resource);
                return $response->toJson();
            } catch (\GuzzleHttp\Exception\ServerException $ex) {
                MissionPillarsLog::log([
                    'event' => 'database_exception',
                    'caller_function' => implode('.', [self::class, __FUNCTION__]),
                    'code' => $ex->getCode(),
                    'message' => $ex->getMessage(),
                    'data' => json_encode($ex)
                ]);
                $response = new ApiJsonResponse(500, $ex->getMessage());
                return $response->toJson();
            }
            catch (\Exception $ex) {
                MissionPillarsLog::log([
                    'event' => 'database_exception',
                    'caller_function' => implode('.', [self::class, __FUNCTION__]),
                    'code' => $ex->getCode(),
                    'message' => $ex->getMessage(),
                    'data' => json_encode($ex)
                ]);
                $response = new ApiJsonResponse(500, $ex->getMessage());
                return $response->toJson();
            } catch (\Illuminate\Database\QueryException $ex){
                MissionPillarsLog::log([
                    'event' => 'database_exception',
                    'caller_function' => implode('.', [self::class, __FUNCTION__]),
                    'code' => $ex->getCode(),
                    'message' => $ex->getMessage(),
                    'data' => json_encode($ex)
                ]);
                
                $response = new ApiJsonResponse(500, $ex->getMessage());
                return $response->toJson();
            }
        }
        $response = new ApiJsonResponse(401, $request->all());
        return $response->toJson();
    }
}
