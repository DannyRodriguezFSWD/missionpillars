@extends('layouts.app')
@push('styles')
    <style>
        td.td_editable::before {
            font-family: FontAwesome;
            content: "\f044   ";
            color: #999;
        }

        td.td_editable {
            cursor: pointer;
            font-weight: bold;
        }

        td.td_editable:hover {
            cursor: pointer;
            font-weight: bold;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <h3 class="card-header">
                    New Menu Items
                </h3>
                <div class="card-body">
                    <table class="table datatable table-hover">
                        <thead>
                        <tr>
                            <th>URI</th>
                            <th>TOOL TIP</th>
                            <th>END DATE</th>
                            <th>CREATED AT</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td class="td_editable" onclick="editItem({{$item}},'uri')">{{$item->uri}}</td>
                                <td class="td_editable"
                                    onclick="editItem({{$item}},'tool_tip')">{{$item->tool_tip}}</td>
                                <td class="td_editable" onclick="editItem({{$item}},'end_at','datetime-local')">{{$item->end_at}}</td>
                                <td>{{$item->created_at}}</td>
                                <td>
                                    <button onclick="deleteItem({{$item}})" class="btn btn-danger">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <h3 class="card-header">
                    Add Menu Item
                </h3>
                <div class="card-body">
                    <form action="{{route('add.new.menu.items.store')}}" method="post">
                        {{csrf_field()}}
                        <div class="form-group">
                            <label for="uri">Uri</label>
                            <input type="text" id="uri" name="uri" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="tool_tip">Tool Tip</label>
                            <input type="text" id="tool_tip" name="tool_tip" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="end_at">End Date</label>
                            <input type="datetime-local" id="end_at" name="end_at" class="form-control">
                        </div>
                        <input type="submit" class="btn btn-primary float-right">
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    {{-- <script src="//cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script> --}}
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    {{-- <script src="//cdn.datatables.net/buttons/1.6.2/js/buttons.flash.min.js"></script> --}}
    <link href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    {{-- <link href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.bootstrap4.min.css" rel="stylesheet"> --}}
    <link rel="stylesheet" href="{{asset('css/custom_datatable.css')}}">
    <script>
        let editItem = async (item, context, type = "text") => {
            Swal.fire({
                title: `Update ${context}`,
                html:
                    `<p>Previous ${context}: ${item[context]}</p>` +
                    `<input id="col" value="${item[context]}" name="col" type="${type}" class="swal2-input">`,
                focusConfirm: false,
                showCancelButton: true,
                showLoaderOnConfirm: true,
                preConfirm: async (val) => {
                    let value = $('#col').val();
                    const result = await axios.patch('/new_menu_items/' + item.id, {context, value})
                }
            }).then(res => {
                if (res.value) {
                    window.location.reload()
                }
            })
        }
        let deleteItem = async (item) => {
            Swal.fire({
                title: 'Are you sure?',
                text: `Delete ${item.uri} on new menu list?`,
                showCancelButton: true,
                cancelButtonColor: 'red',
                preConfirm: async (val) => {
                    const result = await axios.delete('/new_menu_items/' + item.id)
                }
            }).then(res => {
                if (res.value) {
                    window.location.reload()
                }
            })
        }
        $(document).ready(function () {
            $('.datatable').DataTable();
        })
    </script>
@endpush
