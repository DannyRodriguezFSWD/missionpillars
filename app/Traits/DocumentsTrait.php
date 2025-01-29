<?php

namespace App\Traits;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

trait DocumentsTrait
{
    use FileManagerTrait;
    
    public function storeDocument(UploadedFile $file, $folder = null, $resize = false, $public = false, $isTemporary = 1, $tenantId = null)
    {
        if (!$file) {
            return false;
        }
        
        $path = $this->upload($file, $folder, $resize, $public, $tenantId);
        
        if (!$tenantId) {
            $tenantId = auth()->user()->tenant_id;
        }
        
        $data = [
            'tenant_id' => $tenantId,
            'name' => $file->getClientOriginalName(),
            'disk' => env('AWS_ENABLED') ? 's3' : config('filesystems.default'),
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'is_public' => $public,
            'is_temporary' => $isTemporary
        ];
        
        $document = new Document();
        mapModel($document, $data);
        $document->save();
        
        return $document;
    }
    
    public function storeDocumentFromUrl($file, $folder = null, $resize = false, $public = false, $isTemporary = 0)
    {
        if (empty(array_get($file, 'url'))) {
            return false;
        }
        
        $path = $this->downloadFromUrl($file, $folder);
        $fullPath = storage_path('app/'.$path);
        
        $data = [
            'tenant_id' => auth()->user()->tenant_id,
            'name' => array_get($file, 'name').'.'.array_get($file, 'extension'),
            'disk' => env('AWS_ENABLED') ? 's3' : config('filesystems.default'),
            'path' => $path,
            'size' => filesize($fullPath),
            'mime_type' => mime_content_type($fullPath),
            'is_public' => $public,
            'is_temporary' => $isTemporary
        ];
        
        $document = new Document();
        mapModel($document, $data);
        $document->save();
        
        // Delete the local file since we don't need it anymore
        if (env('AWS_ENABLED')) {
            checkAndDeleteFile($fullPath);
        }
        
        return $document;
    }
    
    public function destroyDocument(Document $document)
    {
        if (array_get($document, 'disk') === 's3') {
            Storage::disk('s3')->delete(array_get($document, 'path'));
        } else {
            checkAndDeleteFile(array_get($document, 'absolute_path'));
        }
        
        $document->delete();
    }
    
    public function destroyDocumentById($id)
    {
        $document = Document::find($id);
        
        if ($document) {
            return $this->destroyDocument($document);
        }
        
        return false;
    }
    
    public function duplicateDocuments($documents, $relationId, $relationType) 
    {
        if ($documents) {
            foreach ($documents as $document) {
                $this->duplicateDocument($document, $relationId, $relationType);
            }
        }
    }
    
    public function duplicateDocument(Document $document, $relationId, $relationType)
    {
        $data = [
            'tenant_id' => auth()->user()->tenant_id,
            'relation_id' => $relationId,
            'relation_type' => $relationType,
            'name' => $document->name,
            'disk' => $document->disk,
            'path' => $document->path,
            'size' => $document->size,
            'mime_type' => $document->mime_type,
            'is_public' => $document->is_public,
            'is_temporary' => 0
        ];
        
        $newDocument = new Document();
        mapModel($newDocument, $data);
        $newDocument->save();
        
        return $newDocument;
    }
}
