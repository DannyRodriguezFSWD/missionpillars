<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomFields\StoreCustomField;
use App\Http\Requests\CustomFields\StoreSection;
use App\Http\Requests\CustomFields\UpdateCustomField;
use App\Http\Requests\CustomFields\UpdateSection;
use App\Models\CustomField;
use App\Models\CustomFieldSection;
use Illuminate\Http\Request;

class CustomFieldsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sections = CustomFieldSection::ordered()->get();
        $customFields = CustomField::notImported()->ordered()->get();
        
        return view('settings.custom-fields.index', compact('sections', 'customFields'));
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
    public function store(StoreCustomField $request)
    {
        $customField = new CustomField();
        $customField->tenant_id = auth()->user()->tenant->id;
        $customField->custom_field_section_id = array_get($request, 'custom_field_section_id');
        $customField->type = array_get($request, 'type');
        $customField->name = array_get($request, 'name');
        $customField->code = strtolower(str_replace(' ', '_', array_get($request, 'name'))).'__c';
        
        if ($request->has('pick_list_values')) {
            $ex = explode(PHP_EOL, array_get($request, 'pick_list_values'));
            $exTrim = array_map('trim', $ex);
            $customField->options = implode(',', $exTrim);
        }
        
        $customField->save();
        
        return redirect()->back()->with(['message' => 'Custom field created successfully.']);
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
    public function update(UpdateCustomField $request, $id)
    {
        $customField = CustomField::findOrFail($id);
        $customField->custom_field_section_id = array_get($request, 'custom_field_section_id');
        $customField->name = array_get($request, 'name');
        $customField->code = strtolower(str_replace(' ', '_', array_get($request, 'name'))).'__c';
        
        if ($request->has('pick_list_values')) {
            $ex = explode(PHP_EOL, array_get($request, 'pick_list_values'));
            $exTrim = array_map('trim', $ex);
            $customField->options = implode(',', $exTrim);
        }
        
        $customField->update();
        
        return redirect()->back()->with(['message' => 'Custom field updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        CustomField::destroy($id);
        return redirect()->back()->with('message', 'Custom field deleted successfully.');
    }
    
    public function getCustomFieldEditForm(Request $request, $id)
    {
        if (!$request->ajax()) {
            abort(404);
        }
        
        $customField = CustomField::findOrFail($id);
        
        if (array_get($customField, 'options')) {
            $ex = explode(',', array_get($customField, 'options'));
            $options = implode(PHP_EOL, $ex);
        } else {
            $options = null;
        }
        
        $sections = CustomFieldSection::ordered()->get();
        
        $view = view('settings.custom-fields.edit', compact('customField', 'options', 'sections'))->render();
        
        return response()->json(['html' => $view]);
    }
    
    public function saveOrder(Request $request)
    {
        $order = array_get($request, 'order');
        
        for ($i=0; $i<count($order); $i++) {
            $customField = CustomField::findOrFail($order[$i]);
            $customField->sort = $i;
            $customField->update();
        }
        
        return response()->json(['success' => true]);
    }
    
    public function storeSection(StoreSection $request)
    {
        $customFieldSection = new CustomFieldSection();
        $customFieldSection->tenant_id = auth()->user()->tenant->id;
        $customFieldSection->name = array_get($request, 'name');
        $customFieldSection->save();
        
        return redirect()->back()->with(['message' => 'Section created successfully.']);
    }
    
    public function updateSection(UpdateSection $request, $id)
    {
        $customFieldSection = CustomFieldSection::findOrFail($id);
        $customFieldSection->name = array_get($request, 'name');
        $customFieldSection->update();
        
        return redirect()->back()->with(['message' => 'Section updated successfully.']);
    }
    
    public function destroySection($id)
    {
        CustomFieldSection::destroy($id);
        return redirect()->back()->with('message', 'Section deleted successfully.');
    }
    
    public function saveSectionOrder(Request $request)
    {
        $order = array_get($request, 'order');
        
        for ($i=0; $i<count($order); $i++) {
            $customField = CustomFieldSection::findOrFail($order[$i]);
            $customField->sort = $i;
            $customField->update();
        }
        
        return response()->json(['success' => true]);
    }
    
    public function getCustomFieldSectionEditForm(Request $request, $id)
    {
        if (!$request->ajax()) {
            abort(404);
        }
        
        $section = CustomFieldSection::findOrFail($id);
        
        $view = view('settings.custom-fields.edit-section', compact('section'))->render();
        
        return response()->json(['html' => $view]);
    }
}
