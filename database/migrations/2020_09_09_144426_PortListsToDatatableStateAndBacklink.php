<?php

use App\DataTables\ContactDataTable;
use App\Models\DatatableState;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PortListsToDatatableStateAndBacklink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // port Lists to DatatableState
        $lists = DB::table('lists AS l')
        ->select(
            DB::raw('1 AS is_user_search'),
            'l.id','l.name','l.tenant_id',
            'l.created_by' ,'l.updated_by' ,'l.deleted_at',
            DB::raw('NOW() AS created_at '),   
            DB::raw('GROUP_CONCAT(included.tag_id) AS tag_ids'),
            DB::raw('GROUP_CONCAT(excluded.tag_id) AS excluded_tag_ids')
        )
        ->leftJoin('list_tags AS included', 'l.id','=','included.list_id')
        ->leftJoin('list_not_tags AS excluded', 'l.id','=','excluded.list_id')
        ->whereNull('datatable_state_id')
        ->groupBy('l.id')
        ->get();
        
        echo "Processing {$lists->count()} lists\n\n";
        foreach ($lists as $list) {
            // code...
            $contact_tags = empty($list->tag_ids) ? null : explode(',',$list->tag_ids);
            $contact_excluded_tags = empty($list->excluded_tag_ids) ? null : explode(',',$list->excluded_tag_ids);
            $values = array_merge(
                ContactDataTable::stateDefaults( [
                    'search'=> compact('contact_tags','contact_excluded_tags'),
                ]),
                array_except((array)$list, ['id','tag_ids','excluded_tag_ids'])
            );
            $values['search'] = json_encode($values['search']);
            var_dump($values);
            
            $datatable_state_id = $id = DB::table('datatable_states')->insertGetId( $values );
            DB::table('lists')->where('id', $list->id)->update(compact('datatable_state_id'));
        }
        
        
        // create lists from user saved searches
        $states = DB::table('datatable_states')
        ->where('is_user_search',1)
        ->whereRaw('id NOT IN (SELECT datatable_state_id FROM lists WHERE datatable_state_id IS NOT NULL) AND NOT EXISTS(SELECT 1 FROM lists WHERE lists.name = datatable_states.name)')
        ->get();
        
        echo "\n\nProcessing {$states->count()} saved user searches\n\n";
        $newlists = $states->map(function($state) {
            return [
                'tenant_id' => $state->tenant_id,
                'name' => $state->name,
                'datatable_state_id' => $state->id,
                'created_by' => $state->created_by,
                'updated_by' => $state->updated_by,
                'created_at' => DB::raw('NOW()'),   
                'deleted_at' => $state->deleted_at,
            ];
        })->toArray();
        // var_export($newlists);
        
        if (!empty($newlists))  DB::table('lists')->insert($newlists);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $query = DB::table('lists')->whereNotNull('datatable_state_id')
        ->whereExists(function($query) {
            $query->select(DB::raw(1))
            ->from('datatable_states')
            ->where('is_user_search',1)
            ->whereRaw('datatable_states.id = lists.datatable_state_id AND lists.name = datatable_states.name AND lists.created_at > datatable_states.created_at');
        });
        echo "Deleting {$query->count()} lists rows\n";
        $query->delete();
        
        
        $query = DB::table('lists')->whereNotNull('datatable_state_id')
        ->whereExists(function($query) {
            $query->select(DB::raw(1))
            ->from('datatable_states AS ds')
            ->where('is_user_search',1)
            ->whereRaw('ds.id = lists.datatable_state_id AND lists.created_at < ds.created_at');
        });
        echo "Updating {$query->count()} lists rows\n";
        $query->update(['datatable_state_id'=>null]);
        
        
        $query = DB::table('datatable_states')
        ->where('is_user_search',1)
        ->whereExists(function($query) {
            $query->select(DB::raw(1))
            ->from('lists')
            ->whereRaw('lists.name = datatable_states.name AND lists.created_at < datatable_states.created_at');
        });
        echo "Deleting {$query->count()} datatable_states rows\n";
        $query->delete();
    }
}
