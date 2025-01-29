@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('merge_individual_contacts') !!}
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">&nbsp;</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <h4 class="mb-0">@lang('Merge Individual')</h4>
                        <p>&nbsp;</p>
                        <p>@lang('Use the merge individual tool to combine two duplicate profiles records in your database into a single profile record. By doing so data associated with the two profiles will be merged (e.g. profiles, addresses, transactions, event tickets, forms, etc).')</p>
                    </div>
                    <div class="col-sm-6">
                        @include('merge.includes.menu')
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        {{ Form::text('keep-contact', null, ['class' => 'form-control autocomplete', 'placeholder' => 'Contact\'s Name you want to keep', 'autocomplete' => 'off', 'data-name' => 'keep']) }}
                        {{ Form::hidden('keep') }}
                    </div>
                    <div class="col-sm-6">
                        {{ Form::text('merge-contact', null, ['class' => 'form-control autocomplete', 'placeholder' => 'Contact\'s Name you want to merge', 'autocomplete' => 'off', 'data-name' => 'merge']) }}
                        {{ Form::hidden('merge') }}
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12 text-right">
                        <button type="button" class="btn btn-primary merge">
                            <span class="fa fa-compress"></span>
                            @lang('Merge')
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-footer">&nbsp;</div>
        </div>
    </div>
</div>
@include('merge.includes.view-log-modal')
@push('scripts')
<script type="text/javascript">
    $('.autocomplete').autocomplete({
        source: function( request, response ) {
            // Fetch data
            $.ajax({
                url: "{{ route('contacts.autocomplete') }}",
                type: 'post',
                dataType: "json",
                data: {
                    search: request.term
                },
                success: function( data ) {
                    response( data );
                }
            });
        },
        minLength: 2,
        select: function( event, ui ) {
            var name = $(this).data('name');
            $('input[name="'+name+'"]').val(ui.item.id);
        }
    });

    $('button.merge').on('click', function(e){
        var keep = $('input[name="keep"]').val();
        var merge = $('input[name="merge"]').val();
        if(!keep){
            Swal.fire('Enter the contact you want to keep','','info');
            $('[data-name="keep"]').focus();
            return false;
        }

        if(!merge){
            Swal.fire('Enter the contact you want to merge','','info');
            $('[data-name="merge"]').focus();
            return false;
        }

        Swal.fire({
            title: "Are you sure?",
            text : "Are you sure you want to merge these profiles?",
            type : 'question',
            showCancelButton: true
        }).then(res => {
            if (res.value){
                $('#overlay').show();
                $.ajax({
                    url: '{{ route("ajax.merge.merge.duplicates") }}',
                    method: 'GET',
                    data: { keep: keep, merge: [merge] }
                }).done(function (result) {
                    log(result);
                    $('#overlay').hide();
                    $('.autocomplete').val('');
                    $('input[name="keep"]').val('');
                    $('input[name="merge"]').val('');
                }).fail(function (data) {
                    $('#overlay').hide();
                    Swal.fire("Oops! something wrong happened",'','error');
                });
            }
        })
    });
</script>
@endpush

@endsection
