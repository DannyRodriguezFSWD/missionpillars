@extends('layouts.app')


@section('breadcrumbs')
    {!! Breadcrumbs::render('contacts.tags', $contact) !!}
@endsection

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    @include('people.contacts.includes.card-header')
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="tags-info">
                                {{ Form::open(['route'=>'contacts.tagcontact']) }}
                                {{ Form::hidden('cid', Crypt::encrypt($contact->id)) }}
                                {{ Form::hidden('folder', app('request')->input('folder') ? app('request')->input('folder') : $root->id) }}
                                @include('people.contacts.includes.tags-info')
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@if(Session::has('message'))
@push('scripts')
<script>
    Swal.fire('{{ Session::get('message') }}', '', 'success')
</script>
@endpush
@endif

@endsection
