<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\Contact;
use App\Models\Group;
use App\Models\Address;
use App\Models\Country;
use App\Traits\CountriesTrait;

class AddressesController extends Controller {

    use CountriesTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id, Request $request) {
        $contact = Contact::findOrFail($id);
        $countries = $this->getCountries();
        $data = [
            'contact' => $contact,
            'countries' => $countries
        ];
        return view('addresses.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        try {
            $country = Country::findOrFail(array_get($request, 'country_id'));
            $address = mapModel(new Address(), $request->all());
            $rid = Crypt::decrypt(array_get($request, 'rid'));
            $rtype = Crypt::decrypt(array_get($request, 'rtype'));
            array_set($address, 'relation_id', $rid);
            array_set($address, 'relation_type', $rtype);
            
            array_set($address, 'country', array_get($country, 'iso_3166_3'));
            if (auth()->user()->tenant->addresses()->save($address)) {
                switch ($rtype) {
                    case Group::class:
                        return redirect()->route('groups.editaddress', ['id' => $rid, 'aid' => array_get($address, 'id')])->with('message', __('Address succesfully created'));
                        break;
                    default :
                        return redirect()->route('addresses.edit', ['id' => array_get($address, 'id')])->with('message', __('Address succesfully created'));
                        break;
                }
            }
        } catch (\Illuminate\Contracts\Encryption\DecryptException $ex) {
            return redirect()->route('cheating');
        }
        abort(500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $address = Address::find($id);
        $contact = $address->contact;
        
        $countries = $this->getCountries();
        $data = [
            'address' => $address,
            'contact' => $contact,
            'countries' => $countries,
            'uid' => Crypt::encrypt($id)
        ];
        return view('addresses.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $address = Address::find($id);
        mapModel($address, $request->all());
        array_set($address, 'is_residence', array_get($request, 'is_residence', 0));
        array_set($address, 'is_mailing', array_get($request, 'is_mailing', 0));
        if ($address->update()) {
            $instance = $address->getRelationTypeInstance;
            $classname = get_class($instance);
            switch ($classname){
                case Group::class:
                    return redirect()->route('groups.editaddress', ['id' => array_get($instance, 'id'), 'aid' => array_get($address, 'id')])->with('message', __('Address succesfully updated'));
                    break;
                default :
                    return redirect()->route('addresses.edit', ['id' => $address->id])->with('message', __('Address succesfully updated'));
                    break;
            }
        }
        abort(500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $address = Address::find($id);
        switch (array_get($address, 'relation_type')){
            case Group::class:
                $instance = $address->group;
                $route = 'groups.edit';
            break;
            default :
                $instance = $address->contact;
                $route = 'contacts.edit';
            break;
        }
        $address->delete();
        return redirect()->route($route, ['id' => array_get($instance, 'id')])->with('message', __('Address succesfully deleted'));
    }

    public function editGroupAddress($id, $addressId) {
        $address = Address::find($addressId);
        $group = $address->group;
        
        $countries = $this->getCountries();
        $data = [
            'address' => $address,
            'group' => $group,
            'countries' => $countries,
            'uid' => Crypt::encrypt($id)
        ];
        return view('people.groups.edit_address')->with($data);
    }
    
}
