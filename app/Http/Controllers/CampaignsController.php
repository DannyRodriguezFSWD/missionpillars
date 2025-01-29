<?php

namespace App\Http\Controllers;

use App\Http\Requests\Campaigns\Update;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Campaign;
use App\Models\Purpose;
use App\Http\Requests\Campaigns\StoreCampaign;
use App\Constants;

class CampaignsController extends Controller
{
    const PERMISSION = 'crm-campaigns';

    public function __construct(){
        $this->middleware(function ($request, $next) {
            if(!auth()->user()->tenant->can(self::PERMISSION)){
                return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION]);
            }
            return $next($request);
        });
    }

    private function sort($sort) {
        switch ($sort) {
            case 'type':
                $field = 'type';
                break;
            default :
                $field = 'name';
                break;
        }
        return $field;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view',Campaign::class);
        if ($request->has('sort') && $request->has('order')) {
            $sort = array_get($request, 'sort');
            $order = array_get($request, 'order');
            $field = $this->sort($sort);
            
            //DB::enableQueryLog();
            $campaigns = Campaign::where('id', '>', 1)->orderBy($field, $order);
            //dd(DB::getQueryLog());
            $nextOrder = ($order === 'asc') ? 'desc' : 'asc';
        } else {
            $campaigns =  Campaign::where('id', '>', 1)->orderBy('id', 'desc');
            $sort = null;
            $order = 'asc';
            $nextOrder = 'asc';
        }
        
        //$total = $charts->get();
        $data = [
            'campaigns' => $campaigns->paginate(),
            'sort' => $sort, 
            'order' => $order, 
            'nextOrder' => $nextOrder, 
            'total' => $campaigns->get()->count()
        ];
        //dd($data);
        return view('campaigns.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create',Campaign::class);
        $charts = collect(Purpose::all())->reduce(function ($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);
        $data = [ 'charts' => $charts ];
        
        return view('campaigns.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCampaign $request)
    {
        $campaign = mapModel(new Campaign(), $request->all());
        $campaign->sub_type = Constants::CHART_OF_ACCOUNT_SUBTYPE_GIVINGPAGES;

        if(!auth()->user()->tenant->campaigns()->save($campaign)){
            return redirect()->route('campaigns.index')->with('error', __('An error has occurred'));
        }
        if (auth()->user()->cannot('edit', $campaign)) {
            return redirect()->route('campaigns.index')->with('message', __('Campaign successfully added'));
        }
        
        return redirect()->route('campaigns.edit', ['id' => array_get($campaign, 'id')])->with('message', __('Campaign successfully added'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $campaign = Campaign::findOrFail($id);
        $this->authorize('show',$campaign);
        $data = ['campaign' => $campaign, 'from_c2g' => $campaign->createdFromC2G()];
        return view('campaigns.show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $campaign = Campaign::findOrFail($id);
        $this->authorize('update',$campaign);
        $charts = collect(Purpose::all())->reduce(function($carry, $item){
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);
        
        $contact = null;
        if(array_get($campaign, 'page_type') === 'Missionary'){
            $contact = array_get($campaign->contact, 'first_name').' '. array_get($campaign->contact, 'last_name'). ' ('.array_get($campaign->contact, 'email_1').')';
        }
        
        $data = [
            'campaign' => $campaign,
            'charts' => $charts,
            'from_c2g' => $campaign->createdFromC2G(),
            'contact' => $contact
        ];
        
        return view('campaigns.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Update $request)
    {
        $campaign = $request->campaign_;
        if ($campaign->createdFromC2G()) return redirect()->route('campaigns.edit', ['id' => array_get($campaign, 'id')])->with('error', __('Cannot update campaigns created from Continue to give. You may update this on Continue to Give Application'));
        mapModel($campaign, $request->all());

        if($campaign->update()){
            return redirect()->route('campaigns.edit', ['id' => array_get($campaign, 'id')])->with('message', __('Campaign successfully updated'));
        }
        
        return redirect()->route('campaigns.index')->with('error', __('An error has occurred'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $campaign = Campaign::findOrFail($id);
        $this->authorize('delete',$campaign);
        $campaign->delete();
        return redirect()->route('campaigns.index')->with('message', __('Campaign successfully deleted'));
    }
}
