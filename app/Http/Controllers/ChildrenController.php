<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\Subdomains;
use App\Traits\FamilyTrait;
use App\Models\Contact;
use App\Http\Requests\StoreRelative;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChildrenController extends Controller {

    use Subdomains,
        FamilyTrait;

    private $tenant = null;

    private $childRelations = [
        'Son', 'Daughter', 'Other', 'Granddaughter', 'Grandson', 'Nephew', 'Niece'
    ];
    
    private $childFamilyPosition = [
        'Child', 'Other'
    ];
    
    public function __construct(Request $request) {
        $this->tenant = $this->getTenant($this->getSubdomain($request->getHost()));
        $t = $this->tenant;
        
        $this->middleware(function ($request, $next) use($t){
            if(!$t->can('crm-child-checkin')){
                return redirect()->route('tenant.upgrade.modules.public');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        if (!$this->tenant) {
            abort(404);
        }

        $data = [
            'tenant' => $this->tenant
        ];
        return view('children.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        $data = [
            'tenant' => $this->tenant,
            'relationships' => $this->getFamilyRelationships(),
            'action' => array_get($request, 'action')
        ];

        return view('children.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRelative $request) {
        $contact = null;
        if ($request->has('cid')) {
            try {
                $id = Crypt::decrypt(array_get($request, 'cid'));
                $contact = Contact::findOrFail($id);
                
            } catch (\Illuminate\Contracts\Encryption\DecryptException $ex) {
                return redirect()->route('cheating');
            }
        }
        
        $relative = mapModel(new Contact(), $request->all());
        array_set($relative, 'tenant_id', array_get($this->tenant, 'id'));
        
        array_set($relative, 'created_at', Carbon::now());
        array_set($relative, 'updated_at', Carbon::now());
        $relative->save();
        
        if($request->has('cid') && $contact){
            $sync = [
                array_get($relative, 'id') => [
                    'contact_relationship' => array_get($request, 'contact_relationship'),
                    'relative_relationship' => array_get($request, 'relative_relationship'),
                ]
            ];
            $contact->relatives()->sync($sync, false);
            return redirect()->route('child-checkin.show', ['id' => $id])->with('message', __('New relative has been added successfully'));
        }
        return redirect()->route('child-checkin.show', ['id' => array_get($relative, 'id')])->with('message', __('You have been added successfully'));

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $contact = Contact::where('id', $id)->first();
        
        if (array_get($contact, 'family_id')) {
            $family = Contact::where('family_id', array_get($contact, 'family_id'))->wherein('family_position', $this->childFamilyPosition)->get();
            $familyContactIds = $family->pluck('id');
        } else {
            $family = collect([]);
            $familyContactIds = [];
        }        
        
        $relatives_down = $contact->relatives()->withoutGlobalScopes()->whereIn('relative_relationship', $this->childRelations)->whereNotIn('relative_id', $familyContactIds)->get();
        $relatives_up = $contact->relativesUp()->withoutGlobalScopes()->whereIn('contact_relationship', $this->childRelations)->whereNotIn('contact_id', $familyContactIds)->get();
        
        $allChildren = $family->merge($relatives_down)->merge($relatives_up)->sortBy('first_name');
        $allChildren->values()->all();
        
        $data = [
            'tenant' => $this->tenant,
            'contact' => $contact,
            'allChildren' => $allChildren
        ];

        return view('children.show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

    
    
    /** Additonal route handling methods **/
    
    /**
     * method for child-checkin.about route
     * @param  Request $request [description]
     */
    public function about(Request $request) {
        if (! auth()->user()->can('child-check-in-view')) abort(403);
        return view('children.about');
    }
    
    public function searchParent(Request $request) {
        $keyword = array_get($request, 'keyword');
        if (!$keyword) {
            return redirect('child-checkin.index')->with('error', __('Type Your First Name, Last Name, Email or Phone Number'));
        }

        $found = Contact::whereRaw("CONCAT(IFNULL(first_name,''), ' ', IFNULL(last_name,''), ' ', IFNULL(email_1,''), ' ', IFNULL(cell_phone,''), ' ', IFNULL(phone_numbers_only,'')) like ?", ['%'.$keyword.'%'])
                        ->where(function($query){
                            $query->where(DB::raw('TIMESTAMPDIFF(YEAR, dob, CURDATE())'), '>', 18)
                            ->orWhereNull('dob');
                        })
                        ->where('type', 'person')
                        ->get();                        
        $data = [
            'tenant' => $this->tenant,
            'found' => $found
        ];
        return view('children.parents')->with($data);
    }

    public function createRelative($id) {
        $contact = Contact::where('id', $id)->first();
        
        $data = [
            'tenant' => $this->tenant,
            'contact' => $contact,
            'relationships' => $this->getFamilyRelationships()
        ];

        return view('children.create-relative')->with($data);
    }

}
