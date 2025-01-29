@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('settings.custom-fields.index') !!}
@endsection

@section('content')

<div class="row">
    <div class="col-md-6 offset-md-3">
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#custom-field-modal">
            <i class="fa fa-plus"></i> New Custom Field
        </button>
        
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#custom-field-section-modal">
            <i class="fa fa-plus"></i> Add New Section
        </button>
    </div>
</div>


<div class="row mt-3">
    <div class="col-md-6 offset-md-3">
        @if ($customFields->count() > 0)
        
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-search"></i></span>
            </div>
            <input type="text" class="form-control" placeholder="Search..." aria-label="Search" id="search-custom-fields">
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Default</h5>
            </div>

            <div class="card-body">
                <ul class="list-group cursor-pointer custom-fields-list">
                @foreach ($customFields as $field)
                    @if (!array_get($field, 'custom_field_section_id') || array_get($field, 'custom_field_section_id') === 1)
                    <li class="list-group-item list-group-item-action" data-url="{{ route('settings.custom-fields.get', $field) }}" data-id="{{ array_get($field, 'id') }}">
                        <i class="fa fa-reorder text-muted cursor-all-scroll mr-3"></i>

                        {{ array_get($field, 'name') }}

                        <span class="pull-right text-warning"><i class="fa fa-edit"></i></span>
                    </li>
                    @endif
                @endforeach
                </ul>
            </div>
        </div>
        
        <div id="section-list">
            @foreach ($sections as $section)
            @if (array_get($section, 'id') === 1)
                @continue
            @endif
            
            <div class="card" data-id="{{ array_get($section, 'id') }}">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fa fa-reorder text-muted cursor-all-scroll mr-3"></i>
                        
                        {{ array_get($section, 'name') }}
                        
                        <span class="pull-right cursor-pointer edit-section" data-url="{{ route('settings.custom-fields.get-section', $section) }}"><i class="fa fa-edit"></i></span>
                    </h5>
                </div>

                <div class="card-body">
                    <ul class="list-group cursor-pointer custom-fields-list">
                    @foreach ($customFields as $field)
                        @if (array_get($field, 'custom_field_section_id') === array_get($section, 'id'))
                        <li class="list-group-item list-group-item-action" data-url="{{ route('settings.custom-fields.get', $field) }}" data-id="{{ array_get($field, 'id') }}">
                            <i class="fa fa-reorder text-muted cursor-all-scroll mr-3"></i>

                            {{ array_get($field, 'name') }}

                            <span class="pull-right text-warning"><i class="fa fa-edit"></i></span>
                        </li>
                        @endif
                    @endforeach
                    </ul>
                </div>
            </div>
            @endforeach
        </div>
        
        @else
        <div class="alert alert-info">You have no custom fields.</div>
        @endif
    </div>
</div>

@include('settings.custom-fields.includes.custom-field-modal')
@include('settings.custom-fields.includes.custom-field-edit-modal')
@include('settings.custom-fields.includes.custom-field-section-modal')
@include('settings.custom-fields.includes.custom-field-section-edit-modal')

@push('scripts')
<script>
    $('.custom-fields-list li').click(function () {
        var item = $(this);
        customAjax({
            url: item.data('url'),
            success: function (response) {
                $('#custom-field-edit-modal .modal-body').html(response.html);
                $('#custom-field-edit-modal').modal('show');
            }
        });
    });
    
    $('#search-custom-fields').keyup(function () {
        let search = $(this).val().toLowerCase();
        
        if (search) {
            $('.custom-fields-list li').hide();

            $('.custom-fields-list li').each(function () {
                if ($(this).text().toLowerCase().includes(search)) {
                    $(this).show();
                }
            });
        } else {
            $('.custom-fields-list li').show();
        }
    });
    
    $('#section-list').sortable({
        update: function() {
            customAjax({
                url: '{{ route('settings.custom-fields.save-section-order') }}',
                data: {
                    order: getCustomFieldsSectionOrder($(this))
                },
                success: function () {
                    
                }
            });
        }
    });
    
    $('.custom-fields-list').sortable({
        update: function() {
            customAjax({
                url: '{{ route('settings.custom-fields.save-order') }}',
                data: {
                    order: getCustomFieldsOrder($(this))
                },
                success: function () {
                    
                }
            });
        }
    });
    
    $('.edit-section').click(function () {
        var item = $(this);
        customAjax({
            url: item.data('url'),
            success: function (response) {
                $('#custom-field-section-edit-modal .modal-body').html(response.html);
                $('#custom-field-section-edit-modal').modal('show');
            }
        });
    });
    
    function getCustomFieldsSectionOrder(list) {
        var orderedList = [];
        
        list.find('.card').each(function () {
            orderedList.push($(this).data('id'));
        });
        
        return orderedList;
    }
    
    function getCustomFieldsOrder(list) {
        var orderedList = [];
        
        list.find('li').each(function () {
            orderedList.push($(this).data('id'));
        });
        
        return orderedList;
    }
</script>
@endpush

@endsection
