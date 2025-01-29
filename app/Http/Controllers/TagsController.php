<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Folder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use App\Models\Tag;
use App\Traits\TagsTrait;
use App\Http\Requests\StoreTag;
use App\Http\Requests\UpdateTag;
use App\Http\Requests\DeleteTag;
use App\Http\Requests\ShowTagFolder;
use App\Constants;

class TagsController extends Controller {
    use TagsTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $root = Folder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));
        $data = $this->getDataTree($root, array_get($root, 'id'));

        $folderDropdown = collect(Folder::where('type', 'TAGS')->orderBy('name')->get())->reduce(function($carry, $item){
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);
        array_set($data, 'folderDropdown', $folderDropdown);

        return view('tags.index')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTag $request) {
        $tag = new Tag();
        array_set($tag, 'name', array_get($request, 'tag'));
        array_set($tag, 'folder_id', array_get($request->all(), 'parent'));
        if (!Auth::user()->tenant->tags()->save($tag)) abort(500);
        
        $message = __('Tag successfully created');
        if( $request->has('cid') ){
            try {
                $contact_id = Crypt::decrypt(array_get($request, 'cid'));
                return redirect()->route('contacts.tags', ['id' => $contact_id, 'folder' => array_get($request->all(), 'parent')])->with($message);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $ex) {
                return redirect('cheating');
            }
        }
        
        return redirect(route('tags.show', ['id' => array_get($request->all(), 'parent')]))->with('message', $message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request) {
        $folder = Folder::findOrFail($id);
        $this->authorize('show',$folder);
        $root = Folder::where('tenant_id', null)->get()->first();
        $data = $this->getDataTree($root, $id);

        $folderDropdown = collect(Folder::where('type', 'TAGS')->orderBy('name')->get())->reduce(function($carry, $item){
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);
        array_set($data, 'folderDropdown', $folderDropdown);

        return view('tags.index')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTag $request) {
        $tag = $request->tag_;
        mapModel($tag, $request->all());
        if ($tag->update()) {
            $message = __('Tag successfully updated');
            return redirect(route('tags.show', ['id' => array_get($tag, 'folder_id')]))->with('message', $message);
        }
        abort(500);
    }

    /**
     * Remove the specified resource from storage.
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteTag $request) {
        $tag = $request->tag_;
        $idx = array_get($tag, 'folder_id');
        if ($tag->delete()) {
            $message = __('Tag successfully deleted');
            return redirect(route('tags.show', ['id' => $idx]))->with('message', $message);
        }
        abort(500);
    }

    public function taggedContacts($id, Request $request) {
        $tag = Tag::find($id);
        $path = $this->getTagPath($tag->folder_id);
        $data = [
            'path' => $path,
            'tag' => $tag,
            'contacts' => $tag->contacts()->paginate(),
            'action' => array_get($request, 'action')
        ];
        return view('tags.contacts')->with($data);
    }

    private function getTagPath($id, $path = []) {
        $folder = Folder::find($id);
        if (!is_null($folder->folder_parent_id)) {
            array_push($path, $folder);
            return $this->getTagPath($folder->folder_parent_id, $path);
        }
        array_push($path, $folder);
        return array_reverse($path);
    }

    public function vueCreateTag(Request $request){
        if(!is_null(array_get($request, 'tag')) && !is_null(array_get($request, 'folder'))){
            $tag = new Tag();
            array_set($tag, 'name', array_get($request, 'tag'));
            array_set($tag, 'folder_id', array_get($request, 'folder'));
            array_set($tag, 'tenant_id', array_get(auth()->user(), 'tenant.id'));
            $tag->save();
        }

        $controller = app()->make('App\Http\Controllers\FoldersController');
        $arguments = [];
        return  app()->call([$controller, 'index'], $arguments);
    }

}
