@php
    // used to determine attributes of form elements
    $inputsdisabled = auth()->user()->can('group-update') ? '' : ' disabled';
@endphp
<div class="row">
    <div class="col-md-12">
        <p class="lead bg-faded">@lang('Groups')</p>
    </div>
</div>

<div class="row">
    @if(auth()->user()->can('group-update'))
        <div class="col-12 text-right">
            <button id="btn-submit-contact" type="submit" class="btn btn-primary"><i class="icons icon-note"></i> Save</button>
        </div>
    @endif
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="row mb-3">
            @if (auth()->user()->can('group-update'))
                <div class="col-12 text-right">
                    <a href="{{ route('groups.create', ['id' => $root->id, 'contact' => $contact->id, 'folder' => app('request')->input('folder')]) }}" class="btn btn-success">
                        <span class="icon icon-people"></span> 
                        @lang('New Group')
                    </a>
                </div>
            @endif
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table table-striped" id="items">
                    @foreach($groups as $group)
                    <tr>
                        <td>
                            @if(in_array($group->name, $groupsArray))
                            <input{{$inputsdisabled}} type="checkbox" name="groups[]" checked value="{{ $group->id }}"/>
                            @else
                            <input{{$inputsdisabled}} type="checkbox" name="groups[]" value="{{ $group->id }}"/>
                            @endif
                            <input type="hidden" value="{{ $group->id }}" name="detach[]"/>
                            <span class="icon icon-people"></span> {{ $group->name}}
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>
