@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        @lang('Third party apps integration')
        <button class="btn btn-primary float-right mb-0" data-toggle="modal" data-target="#infoModal">@lang('Add Integration')</button>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($integrations as $integration)
            <div class="col-md-6">
                <div class="card card-inverse card-info">
                    <div class="card-body p-3 clearfix">
                        <div class="btn-group float-right">
                            <button type="button" class="btn btn-transparent active dropdown-toggle p-0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icon-settings"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#">Action</a>
                                <a class="dropdown-item" href="#">Another action</a>
                                <a class="dropdown-item" href="#">Something else here</a>
                            </div>
                        </div>
                        <i class="icon icon-layers bg-info p-3 font-2xl mr-3 float-left"></i>
                        <div class="h5 mb-0 mt-2">{{ $integration->service }}</div>
                        <div class="text-uppercase font-weight-bold font-xs">{{ $integration->description }}</div>
                    </div>
                    <div class="card-footer px-3 py-2">
                        <!--
                        <a class="font-weight-bold font-xs btn-block text-white" href="{{ route('integrations.show', ['id' => $integration->id]) }}">
                            Manage <i class="fa fa-angle-right float-right font-lg"></i>
                        </a>
                        -->
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>



<div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('Add integration')</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Application</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <button type="button" class="btn btn-link" data-dismiss="modal" data-toggle="modal" data-target="#mailchimp">
                                    Mailchimp Email plattform integration
                                </button>
                                <button type="button" class="btn btn-link" data-dismiss="modal" data-toggle="modal" data-target="#c2g">
                                    Continue To Give Donors Plattform
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@include('integration.apps.mailchimp.includes.token-modal')
@include('integration.apps.continuetogive.includes.token-modal')

@endsection
