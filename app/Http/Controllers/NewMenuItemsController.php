<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\NewMenuItem;

class NewMenuItemsController extends Controller
{
    public function index(Request $request)
    {
        $items = NewMenuItem::notEndedYet()->get();
        if ($request->ajax()) {
            return $items;
        }
        
        $this->authorize('show', NewMenuItem::class);
        $items = NewMenuItem::all();
        return view('new_menu_items.index', compact('items'));
    }

    public function update(NewMenuItem $item, Request $request)
    {
        $this->authorize('update', NewMenuItem::class);
        $context = $request->context;
        $item->$context = $request->value;
        $item->save();

    }

    public function destroy(NewMenuItem $item)
    {
        $this->authorize('delete', NewMenuItem::class);
        $item->delete();
    }

    public function store(Request $request)
    {
        $this->authorize('create', NewMenuItem::class);
        $item = new NewMenuItem();
        $item->uri = $request->uri;
        $item->tool_tip = $request->tool_tip;
        $item->end_at = $request->end_at;
        $item->save();
        return redirect(route('add.new.menu.items.index'));
    }
}
