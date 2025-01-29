@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('users.create') !!}
@endsection
@section('content')


<div class="row">
    <div class="col-sm-12">
        @if(!auth()->user()->can('role-change'))
            <div class="alert alert-warning" role="alert">
                <i class="fa fa-exclamation-circle"></i> You do not have the ability to assign roles, this user will have a role that has basic
                permissions.
            </div>
        @endcan
        <div class="card">
            <div class="card-header">
                @include('widgets.back')
            </div>
            <div class="card-body">
                {{ Form::open(['route' => 'users.store']) }}
                <div class="row">
                    <div class="col-sm-12 text-right pb-2">
                        <div class="" id="floating-buttons">
                            <button type="submit" class="btn btn-primary"><i class="icons icon-note"></i> @lang('Save')</button>

                        </div>
                    </div>
                </div>
                <p>&nbsp;</p>
                <div class="row">
                    <div class="col-md-12">

                        <div class="personal-info">
                            @include('people.users.includes.personal-info')
                        </div>
                    </div>
                </div>

                {{ Form::close() }}
            </div>
        </div>

    </div>
    <!--/.col-->

</div>
<!--/.row-->

@push('scripts')
<script type="text/javascript">
    (function () {
        $('.date .input-group-addon').on('click', function (e) {
            $('.datepicker').focus();
        });


    })();
</script>
@endpush
@endsection
