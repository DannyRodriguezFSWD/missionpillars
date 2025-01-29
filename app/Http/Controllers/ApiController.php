<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OauthAccessToken;
use App\Http\Requests\StoreApiKey;

class ApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('show',OauthAccessToken::class);
        $keys = auth()->user()->tenant->tokens()->where('revoked', false)->get();
        $data = [
            'keys' => $keys,
            'total' => count($keys)
        ];

        return view('integration.api.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create',OauthAccessToken::class);
        return view('integration.api.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreApiKey $request)
    {
        $api = auth()->user()->createToken(array_get($request, 'name'));
        $key = OauthAccessToken::findOrFail($api->token->id);

        array_set($key, 'token', $api->accessToken);
        array_set($key, 'tenant_id', auth()->user()->tenant->id);
        $key->update();

        return redirect()->route('api.index')->with('message', __('API Key successfully created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('show',OauthAccessToken::class);
        $key = OauthAccessToken::findOrFail($id);
        $data = [
            'key' => $key
        ];

        return view('integration.api.show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('delete',OauthAccessToken::class);
        $key = OauthAccessToken::findOrFail($id);
        array_set($key, 'revoked', true);
        $key->update();
        return redirect()->route('api.index')->with('message', __('API Key successfully revoked'));
    }
}
