@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('users.edit',$user) !!}
@endsection
@section('content')


<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                @include('widgets.back')
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        &nbsp;
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6">
                                <h1 class="">
                                    {{ isset($user) ? 'Edit User' : 'Add New User' }}
                                </h1>
                            </div>
                            <div class="col-md-6 text-right">
                                <div id="btn-group" class="btn-group btn-group" role="group" aria-label="...">
                                    
                                    <button id="btn-submit" type="submit" class="btn btn-primary">
                                        <i class="icons icon-note"></i> @lang('Save')
                                    </button>
                                    {{ Form::model($user, ['route' => ['users.destroy', $user->id], 'method' => 'delete', 'id'=>'delete-form-'.$user->id]) }}
                                    {{ Form::hidden('uid',  Crypt::encrypt($user->id)) }}
                                    @can('delete',$user)
                                    <button type="button" class="btn btn-danger delete" data-name="{{$user->name}}" data-form="#delete-form-{{$user->id}}" data-toggle="modal" data-target="#delete-modal">
                                        <span class="fa fa-trash"></span> @lang('Delete')
                                    </button>
                                    @endcan
                                    {{ Form::close() }}
                                    
                                </div>

                            </div>
                        </div>
                        {{ Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'put', 'id' => 'form']) }}
                        {{ Form::hidden('uid', $uid) }}
                        <div class="personal-info">
                            @include('people.users.includes.personal-info')
                            {{-- @lang('Force to change password') 
                            <label class="c-switch c-switch-label  c-switch-primary">
                                <input name="force" type="checkbox" class="c-switch-input" value="true" {{ session('check') ? session('check') : '' }}>
                                       <span class="c-switch-slider" data-checked="Yes" data-unchecked="No"></span>

                            </label> --}}
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>

        </div>

    </div>
    <!--/.col-->

</div>
<!--/.row-->

@push('scripts')
<script type="text/javascript">
    (function () {
        $('.datepicker').datepicker({
            format: "mm/dd/yyyy"
        });
        $('.date .input-group-addon').on('click', function (e) {
            $('.datepicker').focus();
        });


        var top = 80;
        $(window).scroll(function () {
            var y = $(this).scrollTop();
            var button = $('#btn-group');
            if (y >= top) {
                button.css({
                    'position': 'fixed',
                    'top': '60px',
                    'right': '36px',
                    'z-index': '99'
                });
            } else {
                button.removeAttr('style')
            }
        });
        
        $('#btn-submit').on('click', function(e){
            $('#form').submit();
        });
    })();
</script>
@endpush
@endsection
