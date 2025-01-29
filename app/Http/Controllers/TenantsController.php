<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Module;
use App\Models\SMSSent;
use App\Traits\Subdomains;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TenantsController extends Controller
{
    use Subdomains;
    
    private $tenant;
    public function __construct(Request $request){
        $this->tenant = $this->getTenant($this->getSubdomain($request->getHost()));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->authorize('update',Tenant::class);
        $domain = str_replace('%s.', '', parse_url(env('APP_DOMAIN'),PHP_URL_HOST));
        $tenant = array_get(auth()->user(),'tenant');
        return view('tenants.edit')->with(compact('tenant','domain'));
    }

    public function updateInfo(Request $request)
    {
        $this->authorize('update',Tenant::class);
        $tenant = array_get(auth()->user(),'tenant');
        $domain = str_replace('%s.', '', parse_url(env('APP_DOMAIN'),PHP_URL_HOST));
        $old_sub = $tenant->subdomain;
        $this->validate($request, [
            'ein' => ['required',Rule::unique('tenants')->ignore($tenant->id),"digits_between:0,16","numeric"],
            'organization' => ['required'],
            //'subdomain' => ['required'],
        ]);
        $tenant->ein = $request->ein;
        $tenant->website = $request->website;
        $tenant->organization = $request->organization;
        //$tenant->subdomain = $request->subdomain;
        $tenant->save();
//        if ($tenant->subdomain != $old_sub) {
//            Auth::logout();
//            $request->session()->invalidate();
//            $request->session()->regenerateToken();
//            return redirect(str_replace($old_sub, $request->subdomain, $request->fullUrl()));
//        }
        return redirect()->back()->with('message', __('Information updated successfully'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(!is_null($this->tenant) && !auth()->check()){
            $link = sprintf(env('APP_DOMAIN'), array_get($item, 'subdomain'));
            return redirect($link);
        }

        if(!is_null($this->tenant) && auth()->check()){
            $this->tenant->upgrade($request);
            return redirect()->route('dashboard.index')->with('message', 'Your plan features were upgraded!');
        }

        return redirect()->route('dashboard.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    //allows autologin and redirect so specific url
    public function go($page, $uuid, Request $request){
        $redirect = null;
        switch($page){
            case 'sms':
                $sent = SMSSent::withoutGlobalScopes()->where('uuid', $uuid)->first();
                if(is_null($sent)){
                    break;
                }

                $content = $sent->content->withoutGlobalScopes()->where('id', array_get($sent, 'sms_content_id'))->first();
                $contact = Contact::withoutGlobalScopes()
                            ->where([
                                ['phone_numbers_only', '=', onlyNumbers(array_get($content, 'sms_phone_number_from'))],
                                ['tenant_id', '=', array_get($content, 'tenant_id')]
                            ])->first();
                $admin = $sent->tenant->users()->withoutGlobalScopes()->first();
                if( !empty($sent) && !empty($admin) && !empty($contact) ){
                    $redirect = redirect()->route('contacts.sms', ['id' => array_get($contact, 'id')]);
                }
                break;
            default:
                break;
        }

        if(!is_null($redirect)){
            return $redirect;
        }

        abort(404);
    }

    public function upgrade(){
        $chms = Module::find(2);
        $acct = Module::find(3);

        return view('tenants.upgrade')->with(compact('chms','acct'));
    }

    public function publicUpgrade(){
        $chms = Module::find(2);
        $acct = Module::find(3);

        return view('tenants.upgrade_public')->with(compact('chms','acct'));
    }
    
}
