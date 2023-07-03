@extends('layouts.master')
@section('title')
    @lang('Help')
@endsection

@section('css')

    <style type="text/css">
        .search-box .form-control {
            padding-left: 40px !important;
            display: inline !important;
            width: auto !important;
        }
        .search-box .search-icon {
            top: -4px !important;
        }
        .td-padding{
            padding-left: 16px !important;
        }

    </style>

@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboard
        @endslot
        @slot('title')
            Help
        @endslot
    @endcomponent

    @include('layouts.messages')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Help List</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="customerList">
                        <div class="row g-4 mb-3">
                            <div class="col-sm-auto">
                            </div>
                            <div class="col-sm">
                                <div class="d-flex justify-content-sm-end">
                                    <div class="search-box ms-2">
                                        <form name="user-search" method="get" action="{{route('admin.helps.index')}}">
                                            <label>
                                                <input value="{{request()->has('search') ? request()->search : ''}}" type="text" name="search" id="search" class="form-control search" placeholder="Search...">
                                                <i class="ri-search-line search-icon"></i>
                                                <button id="search-button" type="submit" class="btn btn-success mb-1">Search</button>
                                            </label>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>






                        <div class="table-responsive table-card mt-3 mb-1">
                            <table class="table align-middle table-nowrap hover" id="customerTable">
                                <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 50px;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="checkAll"
                                                   value="option">
                                        </div>
                                    </th>
                                    <th class="sort" data-sort="customer_name">Account</th>
                                    <th class="sort" data-sort="email">Message</th>
                                    <th class="sort" data-sort="date"> Date</th>
                                    <th class="sort" data-sort="action">Action</th>
                                </tr>
                                </thead>
                                <tbody class="list form-check-all">
                                @foreach($helps as $help)
                                    <tr>
                                        <th scope="row">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="checkAll"
                                                       value="option1">
                                            </div>
                                        </th>
                                        <td class="id" style="display:none;"><a href="javascript:void(0);"
                                                                                class="fw-medium link-primary">{{$help->id}}</a>
                                        </td>
                                        <td class="username">{{$help->user->username ?? ""}}</td>
                                        <td class="customer_name">{{$help->message}}</td>
                                        <td class="date">{{ isset($help->created_at)  ? $help->created_at->format('j F Y') : '' }}</td>


                                        <td>
                                            <div class="d-flex gap-2">

                                                <div class="edit" data-bs-toggle="tooltip" data-bs-placement="top"
                                                     title="Edit">
                                                    <button class="btn btn-sm btn-success edit-item-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editModal_{{$help->id}}"><i
                                                            class="mdi mdi-human-edit"></i></button>
                                                </div>

                                            </div>

                                            <!-- edit modal -->
                                            <div class="modal fade" id="editModal_{{$help->id}}" tabindex="-1"
                                                 aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-light p-3">
                                                            <h5 class="modal-title" id="exampleModalLabel">Add
                                                                Relpy</h5>
                                                            <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"
                                                                    id="close-modal"></button>
                                                        </div>
                                                        <form action="{{ route('admin.helps.update',$help->id) }}"
                                                              method="POST" autocomplete="off"
                                                              enctype="multipart/form-data">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="customername-field" class="form-label">Reply</label>
                                                                    <textarea name="reply" class="form-control"
                                                                              placeholder="Please write here"
                                                                              required>{{$help->reply}}</textarea>
                                                                </div>

                                                            </div>
                                                            <div class="modal-footer">
                                                                <div class="hstack gap-2 justify-content-end">
                                                                    <button type="button" class="btn btn-light"
                                                                            data-bs-dismiss="modal">Close
                                                                    </button>
                                                                    <button type="submit" class="btn btn-success">
                                                                        Update
                                                                    </button>
                                                                    <!-- <button type="button" class="btn btn-success" id="edit-btn">Update</button> -->
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- edit modal end-->


                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>


                        <div class="d-flex justify-content-end">
                            {!! $helps->appends(\Request::except('page'))->render() !!}
                        </div>
                    </div>
                </div><!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->

@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/prismjs/prismjs.min.js') }}"></script>

    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
