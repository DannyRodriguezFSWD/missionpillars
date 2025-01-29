<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateNote;
use App\Models\Note;
use Illuminate\Http\Request;

class NotesController extends Controller {

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
  public function create() {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request) {
    $note = mapModel(new Note(), $request->all());
    
    array_set($note, 'relation_id', $request->get('relation_id'));
    $relationType = $request->get('relation_type');
    array_set($note, 'relation_type', $relationType);
    
    array_set($note, 'user_id', array_get(auth()->user(), 'id'));

    $redirect = $request->get('redirect', route('contacts.notes', $note->relation_id));

    if (empty(array_get($note, 'date'))) {
        array_set($note, 'date', date('Y-m-d'));
    }
    
    if (auth()->user()->tenant->notes()->save($note)) {
      return redirect($redirect)->with('message', 'Note added');
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
    //
  }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateNote $request, $id) 
    {
        $note = Note::findOrFail($id);
        mapModel($note, $request->all());
        $note->save();
        
        return redirect()->back()->with(['message' => 'Note updated successfully.']);
    }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id, Request $request) {
    Note::find($id)->delete();
    $relationId = $request->get('relation_id');
    $relationType = $request->get('relation_type');
    $redirect = $request->get('redirect', route('contacts.notes', $relationId));
    return redirect($redirect)->with('message', 'Note deleted');
  }

}
