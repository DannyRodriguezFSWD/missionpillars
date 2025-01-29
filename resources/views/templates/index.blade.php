
@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('purposes.index') !!}
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">&nbsp;</div>
            <div class="card-body">
               
                <h4>Templates</h4>
               
            </div>
            
            <div class="card-body">
             
                
            
                
                <form action="{{route('templates.fileupload')}}" method="post" enctype="multipart/form-data" class="mb-2">
                    {{csrf_field()}}
                    <input type="file" name="file">
                    <button name="action" value="save" type="submit" class="btn btn-primary mr-2 btn_save">
                        <i class="fa fa-save"></i> Upload Template
                    </button>
                
                </form>
                
                @include ('templates.includes.tiny')
            </div>
            <div class="card-footer">&nbsp;</div>
        </div>
    </div>
</div>

@endsection
