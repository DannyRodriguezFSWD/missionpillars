<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lists\Update;
use App\Models\Lists;
use App\Models\Folder;
use App\Models\Address;
use App\Models\Integration;
use App\Constants;
use Illuminate\Http\Request;
use App\Traits\TagsTrait;
use App\Http\Requests\Lists\StoreList;
use App\Traits\CountriesTrait;
use App\Classes\Mailchimp\Mailchimp;
use App\Models\Contact;
use App\Models\Email;
use DOMDocument;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailSent;
use App\Classes\Shared\Emails\Charts\Pie\PieChart as EmailPieChart;
use Ramsey\Uuid\Uuid;

class ListsController extends Controller {

    use TagsTrait,
        CountriesTrait;
    
    const PERMISSION = 'crm-communications';

    public function __construct(){
        $this->middleware(function ($request, $next) {
            if(!auth()->user()->tenant->can(self::PERMISSION)){
                return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION]);
            }
            return $next($request);
        });
    }

    public function sort($sort) {
        switch ($sort) {
            case 'lastname':
                $field = 'contacts.last_name';
                break;
            case 'email':
                $field = 'contacts.email_1';
                break;
            default :
                $field = 'contacts.first_name';
                break;
        }
        return $field;
    }

    /**
     * Display a listing of the resource.
     * Mostly Deprecated
     * NOTE: for non-ajax requests, this now redirects to Saved Searches
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        if(!$request->ajax()) return redirect(route('search.contacts.state.index'));
        /*
          $mailchimp = new Mailchimp();
          if( is_null($mailchimp->getToken()) ){
          return view('lists.no-api-key');
          }
         */
         $baselists = [["id" => 0, "name" => "Everyone"]];
         $query = Lists::legacy()
         ->orWhere(function($query) { $query->userSavedSearch(); });
         
         // it not a user saved search, ONLY display this list
         if (Lists::userSavedSearch(false)->find($request->get('list_id'))) {
             $query = Lists::where('id',$request->get('list_id'));
             $baselists = [];
         }
         else if ($request->get('list_id')) $query->orWhere('id', $request->get('list_id'));

        if($request->ajax()){
            $result = collect($query->orderBy('name')->get())->reduce(function($carry, $item){
                $option = [
                    "id" => $item->id,
                    "name" => $item->name
                ];
                array_push($carry, $option);
                return $carry;
            }, $baselists);
            return response()->json($result);
        }
        
        $lists = $query->get();
        $total = $lists->count();
        return view('lists.index')->with(compact('lists','total'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $this->authorize('create',Lists::class);
        $address = auth()->user()->tenant->contacts->first()->addressInstance->first();
        $country = \App\Models\Country::where('iso_3166_2', array_get($address, 'country'))->first();
        $root = Folder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));
        $data = $this->getDataTree($root, array_get($root, 'id'));
        
        array_set($data, 'countries', $this->getCountries());
        array_set($data, 'in', []);
        array_set($data, 'not', []);
        array_set($data, 'address', $address);
        array_set($data, 'country', $country);
        array_set($data, 'list', null);

        return view('lists.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreList $request) {
        //$mailchimp = new Mailchimp();
        $in = array_get($request, 'tags', []);
        $not = array_get($request, 'not', []);
        $tags = array_diff($in, $not);

        $list = mapModel(new Lists(), $request->all());
        array_set($list, 'uuid', Uuid::uuid4());
        if (auth()->user()->tenant->lists()->save($list)) {
            $address = mapModel(new Address(), $request->all());
            array_set($address, 'relation_id', array_get($list, 'id'));
            array_set($address, 'relation_type', get_class($list));
            auth()->user()->tenant->addresses()->save($address);

            $list->inTags()->sync(array_get($request, 'tags'));
            $list->notInTags()->sync(array_get($request, 'not'));
            return redirect()->route('lists.edit', ['id' => array_get($list, 'id')])->with('message', __('List successfully added'));
            //return redirect()->route('emails.create', ['list' => array_get($list, 'id')])->with('message', __('List successfully added. Now create the email message'));
            
        }
        abort(500);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Lists  $lists
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request) {
        $list = Lists::findOrFail($id);
        $this->authorize('show',$list);
        $in = array_pluck($list->inTags, 'id');
        $not = array_pluck($list->notInTags, 'id');
        
        $inList = Contact::whereHas('tags', function($query) use ($in){
            $query->whereIn('id', $in);
        })->get();
        
        $notInList = Contact::whereHas('tags', function($query) use ($not){
            $query->whereIn('id', $not);
        })->get();
        
        $tags = array_diff($in, $not);
        //dd($inList, $notInList);
        $ids = array_diff(array_pluck($inList, 'id'), array_pluck($notInList, 'id'));

        if ($request->has('sort') && $request->has('order')) {
            $sort = array_get($request, 'sort');
            $order = array_get($request, 'order');
            $field = $this->sort($sort);
            /*
            $contacts = Contact::join('contact_tag', 'contact_tag.contact_id', '=', 'contacts.id')
                    ->join('tags', 'tags.id', '=', 'contact_tag.tag_id')
                    ->whereIn('tags.id', $tags)
                    ->orderBy($field, $order);
             * 
             */
            $contacts = Contact::whereIn('id', $ids)->orderBy($field, $order);
            $nextOrder = ($order === 'asc') ? 'desc' : 'asc';
        } else {
            /*
            $contacts = Contact::join('contact_tag', 'contact_tag.contact_id', '=', 'contacts.id')
                    ->join('tags', 'tags.id', '=', 'contact_tag.tag_id')
                    ->whereIn('tags.id', $tags)
                    ->orderBy('contacts.id', 'desc');
             * 
             */
            $contacts = Contact::whereIn('id', $ids)->orderBy('contacts.id', 'desc');
            $sort = null;
            $order = 'asc';
            $nextOrder = 'asc';
        }

        $total = count($contacts->get());
        $data = [
            'list' => $list,
            'contacts' => $contacts->paginate(),
            'total' => $total,
            'sort' => $sort,
            'order' => $order,
            'nextOrder' => $nextOrder,
        ];

        return view('lists.show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Lists  $lists
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request) {
        $list = Lists::findOrFail($id);
        $this->authorize('update',$list);
        $email = array_get($request, 'email');
        
        $in = array_pluck($list->inTags, 'id');
        $not = array_pluck($list->notInTags, 'id');

        $root = Folder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));
        $data = $this->getDataTree($root, array_get($root, 'id'));
        array_set($data, 'lists', Lists::all());
        array_set($data, 'total', Lists::all()->count());
        array_set($data, 'countries', $this->getCountries());

        array_set($data, 'list', $list);
        array_set($data, 'in', $in);
        array_set($data, 'not', $not);
        array_set($data, 'email', $email);

        return view('lists.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Lists  $lists
     * @return \Illuminate\Http\Response
     */
    public function update(Update $request, $id) {
        //$mailchimp = new Mailchimp();
        $list = $request->list_;
        $this->authorize('update',$list);
        $currentTags = array_pluck($list->inTags, 'id');

        $in = array_get($request, 'tags', []);
        $not = array_get($request, 'not', []);
        $tags = array_diff($in, $not);

        mapModel($list, $request->all());
        if ($list->update()) {
            $address = $list->addressInstance->first();
            if ($address) {
                mapModel($address, $request->all());
                $address->update();
            }

            $list->inTags()->detach($currentTags);
            $list->inTags()->sync($in);
            $list->notInTags()->sync($not);
            return redirect()->route('lists.edit', ['id' => array_get($list, 'id')])->with('message', __('List successfully added'));
        }
        abort(500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Lists  $lists
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $list = Lists::findOrFail($id);
        $this->authorize('delete',$list);
        if ($list->delete()) {
            return redirect()->route('lists.index')->with('message', __("List has been removed succesfully"));
        }
        
        return redirect()->route('lists.index')->with('error', __("An error occurred trying to delete lists ") . $list->name);
    }

    public function search($id, Request $request) {
        $list = Lists::findOrFail($id);
        $keyword = array_get($request, 'keyword');
        $in = array_pluck($list->inTags, 'id');
        $not = array_pluck($list->notInTags, 'id');
        $tags = array_diff($in, $not);

        if ($request->has('sort') && $request->has('order')) {
            $sort = array_get($request, 'sort');
            $order = array_get($request, 'order');
            $field = $this->sort($sort);

            $contacts = Contact::join('contact_tag', 'contact_tag.contact_id', '=', 'contacts.id')
                    ->join('tags', 'tags.id', '=', 'contact_tag.tag_id')
                    ->where(function($query) use ($keyword) {
                        $query->where('first_name', 'like', "%$keyword%")
                        ->orWhere('last_name', 'like', "%$keyword%")
                        ->orWhere('email_1', 'like', "%$keyword%");
                    })
                    ->whereIn('tags.id', $tags)
                    ->orderBy($field, $order);
            $nextOrder = ($order === 'asc') ? 'desc' : 'asc';
        } else {
            $contacts = Contact::join('contact_tag', 'contact_tag.contact_id', '=', 'contacts.id')
                    ->join('tags', 'tags.id', '=', 'contact_tag.tag_id')
                    ->where(function($query) use ($keyword) {
                        $query->where('first_name', 'like', "%$keyword%")
                        ->orWhere('last_name', 'like', "%$keyword%")
                        ->orWhere('email_1', 'like', "%$keyword%");
                    })
                    ->whereIn('tags.id', $tags);
            $sort = null;
            $order = 'asc';
            $nextOrder = 'asc';
        }

        $total = count($contacts->get());
        $data = [
            'list' => $list,
            'contacts' => $contacts->paginate(),
            'total' => $total,
            'sort' => $sort,
            'order' => $order,
            'nextOrder' => $nextOrder,
        ];
        return view('lists.search')->with($data);
    }

    public function emailSent($id, Request $request) {
        $list = Lists::findOrFail($id);
        $emails = Email::where([
                    ['relation_id', '=', array_get($list, 'id')],
                    ['relation_type', '=', Lists::class]
        ]);

        $data = [
            'list' => $list,
            'emails' => $emails->paginate(),
            'total' => $emails->get()->count()
        ];
        return view('lists.emails.index')->with($data);
    }

    public function emailTrack($listId, $emailId, Request $request) {
        $list = Lists::findOrFail($listId);
        $email = Email::findOrFail($emailId);
        $chart = EmailPieChart::graph($email);
        $sentOut = $email->sent()->paginate();
        $data = [
            'list' => $list,
            'email' => $email,
            'total' => $email->sent()->get()->count(),
            'chart' => $chart,
            'sentOut' => $sentOut
        ];
        
        return view('lists.emails.track-last-status')->with($data);
    }
    
    public function emailTrackHistory($listId, $emailId, $sentId, Request $request) {
        $list = Lists::findOrFail($listId);
        $email = Email::findOrFail($emailId);
        $sent = EmailSent::findOrFail($sentId);
        
        $data = [
            'list' => $list,
            'email' => $email,
            'sent' => $sent
        ];
        
        return view('lists.emails.track-history')->with($data);
    }
    //we are going to move to ajax calls
    public function ajaxGetLists(){
    
    }
}
