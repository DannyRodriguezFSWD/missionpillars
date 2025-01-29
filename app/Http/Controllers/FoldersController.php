<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Folder;
use App\Http\Requests\StoreTagFolder;
use App\Http\Requests\UpdateTagFolder;
use App\Http\Requests\DeleteTagFolder;
use Illuminate\Support\Facades\Crypt;
use App\Constants;
use App\Traits\TagsTrait;

class FoldersController extends Controller {
    use TagsTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        //dd(auth()->user()->tenant->tokens()->first());
        $root = Folder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));
        $tree = $this->formatVueData($root);
        $data = [
            'tree' => $tree,
            'folders' => Folder::all()
        ];
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTagFolder $request) {
        $message = __('Folder successfully added');
        $folder = new Folder();
        array_set($folder, 'name', array_get($request, 'folder'));
        array_set($folder, 'folder_parent_id', array_get($request->all(), 'parent'));
        array_set($folder, 'type', array_get($request, 'type'));

        if (!auth()->user()->tenant->folders()->save($folder)) abort(500);

        // multiple redirects based on the request or type of folder
        if ($request->has('cid')) {
            try {
                $contact_id = Crypt::decrypt(array_get($request, 'cid'));
                return redirect()->route('contacts.tags', ['id' => $contact_id, 'folder' => array_get($request->all(), 'parent')])->with($message);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $ex) {
                return redirect('cheating');
            }
        }
        if (array_get($folder, 'type') === array_get(Constants::TAG_SYSTEM, 'FOLDERS.TYPE.TAG_FOLDER')) {
            return redirect(route('tags.show', ['id' => array_get($request->all(), 'parent')]))->with('message', $message);
        }

        if (array_get($folder, 'type') === array_get(Constants::TAG_SYSTEM, 'FOLDERS.TYPE.GROUP_FOLDER')) {
            //create tag folder and map the group folder
            $this->createNewTagFolder($folder);
            return redirect(route('groups.show', ['id' => array_get($request->all(), 'parent')]))->with('message', $message);
        }
        // still here? abort
        abort(500);
    }

    private function createNewTagFolder($folder) {
        $tagFolderId = array_get(Constants::TAG_SYSTEM, 'FOLDERS.GROUPS');
        $tagFolderName = str_replace(':name:', array_get($folder, 'name'), array_get(Constants::TAG_SYSTEM, 'FOLDERS.GROUP'));

        $tagFolder = Folder::where([
                    ['name', '=', $tagFolderName],
                    ['folder_parent_id', '=', $tagFolderId]
                ])->first();

        if (!$tagFolder) {
            $newTagFolder = new Folder();
            array_set($newTagFolder, 'name', $tagFolderName);
            array_set($newTagFolder, 'folder_parent_id', $tagFolderId);
            array_set($newTagFolder, 'type', array_get(Constants::TAG_SYSTEM, 'FOLDERS.TYPE.TAG_FOLDER'));
            array_set($newTagFolder, 'is_system_autogenerated', true);

            auth()->user()->tenant->folders()->save($newTagFolder);

            array_set($folder, 'map_tag_folder_id', array_get($newTagFolder, 'id'));
            $folder->update();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        echo 'show: ' . $id;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTagFolder $request) {
        $folder = $request->folder_;
        mapModel($folder, $request->all());
        //array_set($folder, 'name', array_get($request->all(), 'name'));
        if ($folder->save()) {
            $message = __('Folder name updated to') . ' ' . array_get($folder, 'name');
            if (array_get($folder, 'type') === 'TAGS') {
                return redirect(route('tags.show', ['id' => array_get($folder, 'folder_parent_id')]))->with('message', $message);
            }
            if (array_get($folder, 'type') === 'GROUPS') {
                $tagFolder = Folder::findOrFail(array_get($folder, 'map_tag_folder_id'));
                $tagFolderName = str_replace(':name:', array_get($folder, 'name'), array_get(Constants::TAG_SYSTEM, 'FOLDERS.GROUP'));
                array_set($tagFolder, 'name', $tagFolderName);
                $tagFolder->update();
                return redirect(route('groups.show', ['id' => array_get($folder, 'folder_parent_id')]))->with('message', $message);
            }
        }
        abort(500);
    }

    private function updateTagFolder($folder) {
        $tagFolderId = array_get(Constants::TAG_SYSTEM, 'FOLDERS.AUTO_GENERATED');
        $tagFolderName = str_replace(':name:', array_get($folder, 'name'), array_get(Constants::TAG_SYSTEM, 'FOLDERS.GROUP'));

        $tagFolder = Folder::where([
                    ['name', '=', $tagFolderName],
                    ['folder_parent_id', '=', $tagFolderId]
                ])->first();

        if (!$tagFolder) {
            $newTagFolder = new Folder();
            array_set($newTagFolder, 'name', $tagFolderName);
            array_set($newTagFolder, 'folder_parent_id', $tagFolderId);
            array_set($newTagFolder, 'type', array_get(Constants::TAG_SYSTEM, 'FOLDERS.TYPE.TAG_FOLDER'));

            auth()->user()->tenant->folders()->save($newTagFolder);

            array_set($folder, 'map_tag_folder_id', array_get($newTagFolder, 'id'));
            $folder->update();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteTagFolder $request) {
        $folder = $request->folder_;
        $idx = array_get($folder, 'folder_parent_id');

        $message = __('Folder was successfully deleted');
        if (array_get($folder, 'type') === 'TAGS') {
            $folder->delete();
            return redirect(route('tags.show', ['id' => $idx]))->with('message', $message);
        }
        if (array_get($folder, 'type') === 'GROUPS') {
            $tagFolder = Folder::findOrFail(array_get($folder, 'map_tag_folder_id'));
            if ($folder->delete()) {
                $tagFolder->delete();
            }
            return redirect(route('groups.show', ['id' => $idx]))->with('message', $message);
        }
        abort(500);
    }

}
