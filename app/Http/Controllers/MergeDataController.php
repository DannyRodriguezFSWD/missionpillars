<?php

namespace App\Http\Controllers;

use App\Models\TransactionSplit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Classes\Shared\MergeData\MergeData;
use Illuminate\Support\Facades\DB;

class MergeDataController extends Controller
{
    public function index(){
        // cannot do union to get both orgs and persons together so have to get creative here
        // union bug fixed in a later laravel update see
        // https://github.com/laravel/framework/pull/26466
//        $duplicates = Contact::select([
//            DB::raw('count(id) as total'),
//            DB::raw('GROUP_CONCAT(id SEPARATOR "-") as duplicated_ids'),
//            'first_name', 'last_name'
//        ])->orderBy('first_name')
//        ->orderBy('last_name')
//        ->groupBy(['first_name', 'last_name'])
//        ->having(DB::raw('count(id)'), '>', 1)
//        ->paginate();
        
        $tenantId = auth()->user()->tenant_id;
        
        $countQuery = "
            select count(*) as count  from (
            (select count(id) as total, GROUP_CONCAT(id SEPARATOR \"-\") as duplicated_ids, `first_name`, `last_name`, company, type from `contacts` 
            where `type` = 'person'
            and `contacts`.`deleted_at` is null 
            and (`contacts`.`tenant_id` = $tenantId or `contacts`.`tenant_id` is null) 
            group by `first_name`, `last_name` 
            having count(id) > 1) 

            union 

            (select count(id) as total, GROUP_CONCAT(id SEPARATOR \"-\") as duplicated_ids, `first_name`, `last_name`, company, type from `contacts` 
            where `type` = 'organization' 
            and `contacts`.`deleted_at` is null 
            and (`contacts`.`tenant_id` = $tenantId or `contacts`.`tenant_id` is null) 
            group by `company` 
            having count(id) > 1 )) as count_table
        ";
        
        $result = DB::select(DB::raw($countQuery));
        $totalCount = array_get($result, '0')->count;
        $perPage = 15;
        $currentPage = array_get(request(), 'page', '1');
        $lastPage = ceil($totalCount / $perPage);
        if ($lastPage == 0) {
            $lastPage = 1;
        }
        $start = $perPage * ($currentPage - 1);
        
        $query = "
            (select count(id) as total, GROUP_CONCAT(id SEPARATOR \"-\") as duplicated_ids, `first_name`, `last_name`, company, type from `contacts` 
            where `type` = 'person'
            and `contacts`.`deleted_at` is null 
            and (`contacts`.`tenant_id` = $tenantId or `contacts`.`tenant_id` is null) 
            group by `first_name`, `last_name` 
            having count(id) > 1) 

            union 

            (select count(id) as total, GROUP_CONCAT(id SEPARATOR \"-\") as duplicated_ids, `first_name`, `last_name`, company, type from `contacts` 
            where `type` = 'organization' 
            and `contacts`.`deleted_at` is null 
            and (`contacts`.`tenant_id` = $tenantId or `contacts`.`tenant_id` is null) 
            group by `company` 
            having count(id) > 1 )

            order by case when type = 'person' then first_name else company end, last_name
            
            limit $start, $perPage
        ";
        
        $duplicates = DB::select(DB::raw($query));
        
    	$data = [
            'duplicates' => $duplicates,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'totalCount' => $totalCount
        ];
        
        return view('merge.index')->with($data);
    }

    public function individual(){
        return view('merge.individual');
    }

    public function ajaxViewDuplicates(Request $request){
        $in = explode('-', array_get($request, 'query', '[]'));
        $duplicates = Contact::whereIn('id', $in)->orderBy('id', 'desc')->get();
        $view = view('merge.includes.view-duplicates', ['duplicates' => $duplicates])->render();
        return response($view);
    }

    public function viewContact(Request $request){
        $id = $request->id;
        $contact = Contact::findOrFail($id);

        $completedtransactions = $contact->transactionSplits()->completed()->get();

        $total_amount_last_year = TransactionSplit::whereHas('transaction', function($q) use ($contact){
            $q->whereBetween('transaction_initiated_at', [
                Carbon::now()->subYear()->startOfYear(),
                Carbon::now()->subYear()->endOfYear()
            ])->where('contact_id', array_get($contact, 'id'))
                ->where('status', 'complete');
        })->sum('amount');

        $timeline = $contact->timeline(1);
        $data = [
            'contact' => $contact,
            'tasks' => $contact->tasks,
            'completedtransactions' => $completedtransactions,
            'timelines' => $timeline,
        ];
        $view = view('merge.includes.contact_view')->with($data)->render();
        return response($view);
    }

    public function ajaxMergeDuplicates(Request $request){
        $merge = new MergeData();
        $keep = array_get($request, 'keep', null);
        $delete = array_get($request, 'merge', null);

        $result = null;
        if(!empty($keep) && !empty($delete)){
            $result = $merge->all($keep, $delete);
        }

        return response()->json($result);
    }
}
