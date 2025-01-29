@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('search.contacts.state.index') !!}
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <a class="btn btn-primary m-2 float-right" href="{{route('search.contacts')}}">Create New Saved Search <i class="fa fa-search-plus"></i></a>
        </div>
        <div class="col-md-12">
{{--            <div class="alert alert-info" role="alert">--}}
{{--                <h4 class="alert-heading">Looking for Lists?</h4>--}}
{{--                <p>"Saved Searches" is a major upgrade from our previous Lists feature and part of Advanced Contact--}}
{{--                    Search. In addition to selecting contacts based on included and excluded tags, Advanced Contact--}}
{{--                    Search can select contacts based textual filters and transaction data. Any existing lists you may--}}
{{--                    have had have been converted to saved searches, can be used as-is, or modified to take advantage of--}}
{{--                    the new filters.</p>--}}
{{--            </div>--}}
            <div class="card">
                <h3 class="card-header">Saved Search</h3>
                <div class="card-body">
                    <table class="table table-hover datatable">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Created At</th>
                            @if(auth()->user()->canDo('viewAll') || auth()->user()->can('communications-menu'))
                                <th>
                                    Actions
                                </th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($states as $state)
                            <tr>
                            <td>{{$state->name}}</td>
                            <td>{{$state->created_at}}</td>
                            @if(auth()->user()->canDo('viewAll') || auth()->user()->can('communications-menu'))
                                <td>
                                    @can('viewAll',\App\Models\Contact::class)
                                        <a class="btn btn-primary" data-toggle="tooltip" title="View" href="/{{$state->uri .'?state_id=' .$state->id}}"><i
                                                    class="fa fa-external-link"></i></a>
                                    @endcan
                                    @if(auth()->user()->can('communications-menu'))
                                        <button onclick="createCommunication({{$state}},'{{$state->uri}}','{{$state->name}}')"
                                                class="btn btn-warning"
                                                data-toggle="tooltip" title="Create Communication"><i
                                                    class="fa fa-envelope"></i></button>
                                        <button onclick="sendSMS({{$state}},'{{$state->uri}}','{{$state->name}}')"
                                                class="btn btn-success"
                                                data-toggle="tooltip" title="Send SMS"><i
                                                    class="fa fa-comment"></i></button>
                                    @endif
                                </td>
                            @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script>
        let createCommunication = async (state, uri, name) => {
            state.name = name
            const {data} = await axios.post(`/${uri}/communication`, state)
            if (data.communication === undefined){
                Swal.fire('Sorry','failed to create communication','error')
            }else{
                location.href = `/crm/communications/${data.communication.id}/edit`
            }
        }
        let sendSMS = async (state, uri, name) => {
            state.name = name
            const {data} = await axios.post(`/${uri}/sms`, state)
            if (data.sms === undefined){
                Swal.fire('Sorry','failed to create SMS Message','error')
            }else{
                location.href = `/crm/communications/sms/${data.sms.id}/edit`
            }
        }
        $(document).ready(function () {
            $('.table').DataTable({
                dom: "<'row mb-2'<'col-sm-12 col-md-12'B>>" +
                "<'row mb-2'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                "<'row mb-2'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12'p>>"
            });
        })
    </script>
@endpush
