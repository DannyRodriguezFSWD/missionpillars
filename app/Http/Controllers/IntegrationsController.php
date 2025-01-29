<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Integration;
use App\Models\IntegrationValue;

class IntegrationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('third-party-apps-view')) abort(403);
        $data = [
            'integrations' => Integration::all()
        ];
        
        return view('integration.apps.index')->with($data);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('third-party-apps-create')) abort(403);

        $fields = $request->except(['_token', 'service', 'description']);
        $integration = mapModel(new Integration(), $request->all());
        if( auth()->user()->tenant->integrations()->save($integration) ){
            foreach ($fields as $key => $value) {
                $values = new IntegrationValue();
                array_set($values, 'key', $key);
                array_set($values, 'value', $value);
                array_set($values, 'tenant_id', array_get($integration, 'tenant_id'));
                $integration->values()->save($values);
            }
            return redirect()->route('integrations.index')->with('message', $integration->service.' service successfully added');
        }
        abort(500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        if (!auth()->user()->can('third-party-apps-view')) abort(403);

        $integration = Integration::findOrFail($id);
        if($integration){
            $service = array_get($integration, 'service');
            switch (strtolower($service)){
                case 'mailchimp':
                    return redirect()->route('mailchimp.index', ['id' => $id]);
                    break;
                case 'continue to give':
                    return redirect()->route('continuetogive.index', ['id' => $id]);
                    break;
                default :
                    abort(404);
                    break;
            }
        }
        redirect('cheating');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('third-party-apps-update')) abort(403);
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
        if (!auth()->user()->can('third-party-apps-update')) abort(403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('third-party-apps-delete')) abort(403);
    }
}
