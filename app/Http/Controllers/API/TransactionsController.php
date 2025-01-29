<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Marcelgwerder\ApiHandler\Facades\ApiHandler;
use App\Classes\ApiJsonResponse;
use App\Classes\Transactions;
use App\Models\Transaction;
use App\Constants;

class TransactionsController extends ApiController {

    public function __construct() {
        parent::__construct();
    }

    public function getTransactions(Request $request) {
        try {
            $transaction = Transaction::query();
            $result = ApiHandler::parseMultiple($transaction)->getResult();
            $response = new ApiJsonResponse(200, $result);
            return $response->toJson();
        } catch (\Illuminate\Database\QueryException $ex) {
            $debug = env('APP_DEBUG');
            $response = $debug ? new ApiJsonResponse(400, $ex) : new ApiJsonResponse(400);
        }
        return $response->toJson();
    }

    public function postTransactions(Request $request) {
        if ($request->header('content-type') === array_get(Constants::API, 'REQUEST_JSON_TYPE')) {
            try {
                $apiTransaction = new Transactions();
                $transaction = $apiTransaction->singleTransaction($request);

                $response = new ApiJsonResponse(200, $transaction);
                return $response->toJson();
            } catch (\GuzzleHttp\Exception\ServerException $ex) {
                $response = new ApiJsonResponse(401, $ex);
                return $response->toJson();
            }
        }
        $response = new ApiJsonResponse(401);
        return $response->toJson();
    }

}
