@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('tenants.edit',$tenant) !!}
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        @include('widgets.back')
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">

                        </div>
                        <div class="col-md-6">
                            <h1>Edit {{$tenant->organization}}</h1>
                            <form action="{{route('tenants.updateInfo')}}" method="post">
                                {{csrf_field()}}
                                <div class="form-group">
                                    <label for="ein">Organization</label>
                                    <input type="text" value="{{$tenant->organization}}" name="organization" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="ein">Website</label>
                                    <input type="text" value="{{$tenant->website}}" name="website" class="form-control">
                                </div>
                                <div class="form-group">
                                    
                                    <label for="ein" class="w-100">
                                        Sub Domain 
                                        <!--<span class="text-danger font-weight-bold float-right"><i class="fa fa-warning"></i>Changing subdomain will log you out</span>-->
                                    </label>
                                    <p>{{$tenant->subdomain}}.{{ $domain }}</p>
                                    @if(false)
                                    <div class="input-group">
                                        <input type="text" value="{{$tenant->subdomain}}" name="subdomain"
                                               class="form-control">
                                        <span class="input-group-append">
                                            <span class="input-group-text">.{{ $domain }}</span>
                                        </span>
                                    </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="ein">EIN (Federal Tax ID)</label>
                                    <input type="text" name="ein" value="{{$tenant->ein}}" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary float-right">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
