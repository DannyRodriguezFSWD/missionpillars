<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\StatementTemplate;

class StatementTemplateController extends Controller
{
    public function store(Request $request) 
    {
        $template = new StatementTemplate();
        $template->fill($request->input());
        $template->tenant_id = auth()->user()->tenant_id;
        if (!$template->save()) {
            abort(500, 'Error saving template');
        }
        
        return response()->json($template);
    }
    
    public function update(Request $request, $id) 
    {
        $template = StatementTemplate::find($id);
        $template->fill($request->except(['id', 'content_html_encoded']));
        if (!$template->save()) {
            abort(500, 'Error updating template');
        }
        
        return response()->json($template);
    }
    
    public function destroy($id) 
    {
        $template = StatementTemplate::findOrFail($id);
        $template->delete();
    }
    
    public function loadTemplates()
    {
        $content_templates = StatementTemplate::all()->map(function ($template) {
            // we need this encoded version to change tempalte preview from desktop to mobile
            $template->content_html_encoded = htmlentities($template->content);
            return $template;
        });
        
        return [
            'view' => view('communications.includes.templatecontainer', compact('content_templates'))->render(),
            'content_templates' => $content_templates->keyBy('id')->toJson()
        ];
    }
}
