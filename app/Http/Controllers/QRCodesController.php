<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchasedTicket;
use Carbon\Carbon;

class QRCodesController extends Controller
{
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(array_get($request, 'action') === 'event-checkin'){
            $ticket = PurchasedTicket::findOrFail(array_get($request, 'id'));
            if(array_get($ticket, 'used')){
                return redirect()->route('qr.show', ['id' => array_get($request, 'action'), 't' => array_get($ticket, 'id')]);
            }
            array_set($ticket, 'checked_in', true);
            array_set($ticket, 'used', true);
            array_set($ticket, 'used_at', Carbon::now());
            $ticket->update();
            return redirect()->route('qr.show', ['id' => 'event-checkin', 't' => array_get($ticket, 'id')])->with('message', 'Complete! You  have used your ticket');
            //return redirect()->route('events.share', ['id' => array_get($ticket, 'registry.event.uuid')])->with('message', 'Complete! You  have used your ticket');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        if( $id === 'event-checkin' ){
            return $this->eventCheckin($request);
        }
        abort(404);
    }

    public function eventCheckin($request) {
        $ticket = PurchasedTicket::findOrFail(array_get($request, 't'));
        $data = [
            'ticket' => $ticket,
            'action' => 'event-checkin'
        ];
        
        if(array_get($ticket, 'used')){
            return view('qrcodes.events.ticket-used')->with($data);
        }
        
        return view('qrcodes.events.checkin-confirm')->with($data);
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
        //
    }
}
