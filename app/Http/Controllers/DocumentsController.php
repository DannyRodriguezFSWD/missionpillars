<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Traits\DocumentsTrait;
use Illuminate\Http\Request;

class DocumentsController extends Controller
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
    public function store(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|max:20480|mimes:pdf,docx,doc,xls,xlsx,txt,csv,jpg,bmp,png,jpeg'
        ]);
        
        $file = $request->file('file');
        
        if (!$file) {
            return false;
        }
        
        $path = $this->upload($file, array_get($request, 'folder'));
        
        $data = [
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $file->getClientOriginalName(),
            'disk' => env('AWS_ENABLED') ? 's3' : config('filesystems.default'),
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'is_temporary' => 1
        ];
        
        if ($request->has('relation_id')) {
            array_set($data, 'relation_id', array_get($request, 'relation_id'));
            array_set($data, 'relation_type', array_get($request, 'relation_type'));
            array_set($data, 'is_temporary', 0);
        }
        
        $document = new Document();
        mapModel($document, $data);
        $document->save();
        
        $html = view('documents.includes.document')->with(compact('document'))->render();
        
        return response()->json(['success' => true, 'id' => array_get($document, 'uuid'), 'html' => $html]);
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
    public function destroy($uuid)
    {
        $document = Document::where('uuid', $uuid)->firstOrFail();
        
        $this->destroyDocument($document);
        
        return response()->json(['success' => true]);
    }
    
    public function downloadDocument($uuid)
    {
        $document = Document::where('uuid', $uuid)->firstOrFail();
        return $this->download(array_get($document, 'absolute_path'), array_get($document, 'name'));
    }
}
