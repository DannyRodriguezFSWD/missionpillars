<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Constants;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Writer\Html;
use PhpOffice\PhpWord\Shared\Converter;

use App\Http\Requests\FileRequest;
use Illuminate\Support\Facades\Storage;

class TemplatesController extends Controller
{
    const PERMISSION = 'crm-purposes';

    public function __construct(){
        $this->middleware(function ($request, $next) {
            if(!auth()->user()->tenant->can(self::PERMISSION)){
                return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION]);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       
        // Load the Word document
       
        $html = "no template";
        return view('templates.index',['html' => $html]);

       

    }

    public function uploadFile(FileRequest $request)
    {
         // Validate file upload
        $validator = Validator::make($request->all(), $request->rules());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Get the uploaded file
        $file = $request->file('file');

        // Check if file is a valid Word document
        $allowedExtensions = ['docx'];
        if (!in_array($file->getClientOriginalExtension(), $allowedExtensions)) {
            return redirect()->back()->withErrors(['File is not a valid Word document.']);
        }

        // Store the file
        try {
            $path = $file->store('files', 'public');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['Failed to store file: ' . $e->getMessage()]);
        }

        // Process the Word document
        return $this->processWordDocument($path);


    
    }
    
    private function processWordDocument($path)
    {
        // Use PhpOffice\PhpWord to load the document
        try {
            // Get a handle to the word document
            $phpWord = IOFactory::load('storage/'.$path);

            // Create HTML writer
            $writer = IOFactory::createWriter($phpWord, 'HTML');

            // Start output buffering
            ob_start();
            // Save the HTML to the output buffer
            $writer->save('php://output');
            $html = ob_get_contents(); // Get the contents of the output buffer
            ob_end_clean(); // Clean the output buffer

            // Return the HTML
            return view('templates.index',['html' => $html]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['Failed to process file: ' . $e->getMessage()]);
        }
    }
  
}
