@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('merge_contacts') !!}
@endsection
@push('styles')
    <link href="{{ asset('css/timeline.custom.css') }}?t={{ time() }}" rel="stylesheet">
@endpush
@section('content')
    @include('includes.overlay')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">&nbsp;</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <h4 class="mb-0">@lang('Show duplicates')</h4>
                        <p>&nbsp;</p>
                        <p>@lang('Shows potential duplicates and allows to merge its profiles, transactions, addresses, form entries, event tickets and all their relationships.')</p>
                    </div>
                    <div class="col-sm-6">
                        @include('merge.includes.menu')
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12">
                        <h6>@lang('Found') <span id="count">{{ $totalCount }}</span> @lang('Potential Duplicate'){{ $totalCount > 1 ? 's':'' }}</h6>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="ids"/>
            <table id="duplicates" class="table table-striped">
                <tbody>
                    @foreach($duplicates as $item)
                    <tr class="{{ $item->duplicated_ids }}">
                        <td>
                            @if ($item->type === 'organization')
                                {{ $item->company }}
                            @else
                                {{ $item->first_name }} {{ $item->last_name }}
                            @endif
                        </td>
                        <td class="text-right">
                            <button data-value="{{ $item->duplicated_ids }}" class="btn btn-link show-duplicates">@lang('View') {{ $item->total }} @lang('Duplicates')</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="card-body"></div>
            <div class="card-footer">
                @foreach( range( max( 1, $currentPage-5 ),
                min( $currentPage+5, $lastPage ) ) 
                as $page )
                @if ($page == $currentPage)
                    <div style="display:inline-block"> {{ $page }} </div>
                @else
                    <div style="display:inline-block"> <a href="merge?page={{$page}}">{{ $page }}</a> </div>
                @endif
            @endforeach
            </div>
        </div>
    </div>
</div>
@include('merge.includes.view-duplicate-modal')
@include('merge.includes.view-log-modal')
@push('scripts')
<script type="text/javascript">
    $('.show-duplicates').on('click', function(e){
        $('#overlay').show();
        var ids = $(this).data('value');
        $('input[name="ids"]').val(ids);
        $.ajax({
            url: '{{ route("ajax.merge.view.duplicates") }}',
            method: 'GET',
            data: { query: ids }
        }).done(function (data) {
            var modal = $('#view-duplicate-modal');
            modal.find('.modal-body').html(data);
            modal.modal('show');
            $('#overlay').hide();
        }).fail(function (data) {
            Swal.fire("Oops! something wrong happened",'','error');
            $('#overlay').hide();
        });
    }).on('hidden.coreui.modal', function () {
        $('#view-duplicate-modal').find('.modal-body').html().empty();
    });

    $('#merge-duplicates').on('click', function(e){
        var modal = $('#view-duplicate-modal');
        var number = modal.find('.modal-body').find('.row');

        Swal.fire({
            title: "Are you sure you?",
            text: "Are you sure you want to merge "+ number.length +" profiles?",
            type: 'question',
            showCancelButton: true
        }).then(res => {
            if (res.value){
                var keep = 0;
                var merge = [];
                $('#log').find('tbody').empty();
                modal.find('.modal-body').find('input[name="item"]').each(function(index, item){
                    if(item.checked){
                        keep = item.value;
                    }
                    else{
                        merge.push(item.value);
                    }
                });
                $('#overlay').show();
                $.ajax({
                    url: '{{ route("ajax.merge.merge.duplicates") }}',
                    method: 'GET',
                    data: { keep: keep, merge: merge }
                }).done(function (result) {
                    $('#overlay').hide();
                    var ids = $('input[name="ids"]').val();
                    $('tr.'+ids).remove();
                    log(result);

                    var total = parseInt($('#count').html());
                    total--;
                    $('#count').html(total)
                    modal.modal('hide');
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
