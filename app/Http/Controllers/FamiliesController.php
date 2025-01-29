<?php

namespace App\Http\Controllers;

use App\Http\Requests\FamilyAddContactRequest;
use App\Http\Requests\FamilyRequest;
use App\Models\Contact;
use App\Models\Family;
use App\Traits\DocumentsTrait;
use Illuminate\Http\Request;

class FamiliesController extends Controller
{
    use DocumentsTrait;
    
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
    public function store(FamilyRequest $request)
    {
        $family = mapModel(new Family(), $request->all());
        array_set($family, 'tenant_id', array_get(auth()->user(), 'tenant_id'));
        $family->save();
        
        return $family;
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(FamilyRequest $request, $id)
    {
        $family = Family::findOrFail($id);
        mapModel($family, $request->all());
        
        if ($family->update()) {
            if ($request->has('removeCoverImage')) {
                $this->destroyDocumentById(array_get($family, 'image_id'));
                array_set($family, 'image_id', null);
                $family->update();
            }
            
            if ($request->hasFile('image') && $request->file('image')->isValid() && !$request->has('removeCoverImage')) {
                if (array_get($family, 'image_id')) {
                    $this->destroyDocumentById(array_get($family, 'image_id'));
                }
                
                $document = $this->storeDocument($request->file('image'), 'family_images', true, true);
                array_set($family, 'image_id', array_get($document, 'id'));
                $family->update();
                array_set($document, 'relation_id', array_get($family, 'id'));
                array_set($document, 'relation_type', get_class($family));
                array_set($document, 'is_temporary', 0);
                $document->update();
            }
            
            return response()->json(['message' => __('Family updated successfully')]);
        }
        
        abort(500);
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
    
    public function addContact(FamilyAddContactRequest $request, $id)
    {
        $contact = Contact::findOrFail(array_get($request, 'contact_id'));
        $family = Family::findOrFail($id);
        array_set($contact, 'family_id', array_get($family, 'id'));
        array_set($contact, 'family_position', array_get($request, 'family_position'));
        $contact->update();
                
        $html = view('people.families.includes.contact')->with(compact('contact'))->render();
        
        return response()->json(['message' => __('Family updated successfully'), 'html' => $html]);
    }
    
    public function familyInfo(Request $request) 
    {
        $family = Family::with('contacts')->findOrFail(array_get($request, 'family_id'));
        
        $html = view('people.families.includes.info')->with(compact('family'))->render();
        
        return response()->json(['family_name' => array_get($family, 'name'), 'html' => $html]);
    }
}
