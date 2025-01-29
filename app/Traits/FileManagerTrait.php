<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

trait FileManagerTrait 
{
    private $resizeX = 400;
    private $resizeY = 400;
    private $tenantId;
    
    public function upload(UploadedFile $file, $folder = null, $resize = false, $public = false, $tenantId = null)
    {
        $this->tenantId = $tenantId;
        
        if ($resize) {
            return $this->uploadAndResize($file, $folder, $public);
        } else {
            return $this->uploadRaw($file, $folder);
        }
    }
    
    public function uploadRaw(UploadedFile $file, $folder = null)
    {
        $path = $this->getUploadPath($folder);
        
        if (env('AWS_ENABLED')) {
            Storage::disk('s3')->put($path, $file, 'public');
            $path.= '/'.$file->hashName();
        } else {
            Storage::putFile($path, $file);
            $path.= '/'.$file->hashName();
        }
        
        return $path;
    }
    
    public function uploadAndResize(UploadedFile $file, $folder = null, $public = false)
    {
        $imageResize = Image::make($file)->resize($this->resizeX, $this->resizeY);
        
        $path = $this->getUploadPath($folder);
        
        if (env('AWS_ENABLED')) {
            $path.= '/'.$file->hashName();
            Storage::disk('s3')->put($path, $imageResize->stream(), 'public');
        } else {
            $fullPath = $public ? storage_path('app/public/'.$path) : storage_path('app/'.$path);
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 755, true);
            }
            $fullPath.= '/'.$file->hashName();
            $imageResize->save($fullPath);
            $path.= '/'.$file->hashName();
        }
        
        return $path;
    }
    
    public function download(string $path, string $name = null)
    {
        if (env('AWS_ENABLED')) {
            $url = Storage::disk('s3')->url($path);
            return redirect($url);
        } else {
            return Response::download($path, $name);
        }
    }
    
    private function getUploadPath($folder = null)
    {
        if (!$this->tenantId) {
            $this->tenantId = auth()->user()->tenant_id;
        }
        
        $path = 'uploads/tenant_'.$this->tenantId;
        
        if ($folder) {
            $path.= '/'.$folder;
        }
        
        return $path;
    }
    
    public function downloadFromUrl($file, $folder = null)
    {
        if (empty(array_get($file, 'url'))) {
            return false;
        }
        
        $content = file_get_contents(array_get($file, 'url'));
        
        if (!$content) {
            return false;
        }
        
        $path = $this->getUploadPath($folder);
        $path.= '/'.Str::random(40).'.'.array_get($file, 'extension');
        
        if (env('AWS_ENABLED')) {
            Storage::disk('s3')->put($path, $content, 'public');
        }
        
        // Always store locally because we need to get some file info
        Storage::put($path, $content);
        
        return $path;        
    }
}
