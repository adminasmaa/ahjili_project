@extends('layouts.master')
@section('title')
    @lang('Report Abuse Posts') 
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
</style>

@endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Dashboard @endslot
    @slot('title') Report Abuse Posts @endslot
@endcomponent

@include('layouts.messages')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Report Abuse Posts List</h4>
            </div><!-- end card header -->

            <div class="card-body">
                <div id="customerList">
                    <div class="row g-4 mb-3">
                        <div class="col-sm-auto col-sm-4">
                        </div>
                        <div class="col-sm-8">
                            <div class="d-flex justify-content-sm-end">
                                <div class="search-box ms-2">
                                <form name="user-search" method="get" action="{{route('admin.report-abuse-posts.index')}}">
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
                                    <th>#</th>
                                    <th>Post</th>
                                    <th>Post Type</th>
                                    <th>Reason</th>
                                    <th>Report Count</th>
                                    <th>Report Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                @foreach($alldata as $singldata)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td><a href="{{route('admin.posts.show',['id' =>$singldata->post_id ])}}">{{short_string($singldata->post->body) ?? ""}}</a></td>
                                    <td>{{ucfirst($singldata->post->type) ?? ""}}</td>
                                    <td>{{short_string($singldata->reason)}}...</td>
                                    <td>{{count($singldata->post->reportAbusePost) ?? 0}}</td>
                                    <td>{{globaldate($singldata->created_at)}}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                        <div class="edit" data-bs-toggle="tooltip" data-bs-placement="top" title="Block User">
                                        <button class="btn btn-sm btn-success edit-item-btn"
                                        data-bs-toggle="modal" data-bs-target="#editModal_{{$singldata->id}}" ><i class="mdi mdi-human-edit"></i></button>
                                        </div>
                                        <div class="remove" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                        <button class="btn btn-sm btn-danger remove-item-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteRecordModal_{{$singldata->id}}"><i class="mdi mdi-delete"></i></button>
                                        </div>

                                        <!-- Modal -->
                                        <div class="modal fade zoomIn" id="deleteRecordModal_{{$singldata->id}}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                                        id="btn-close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{route('admin.report-abuse-posts.destory',$singldata->id)}}" method="post">
                                                        @method('delete')
                                                        @csrf()
                                                        <div class="mt-2 text-center">
                                                            <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop"
                                                                colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                                                            <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                                                                <h4>Are you Sure ?</h4>
                                                                <p class="text-muted mx-4 mb-0">Are you Sure You want to Remove this Record ?</p>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                                                            <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn w-sm btn-danger " id="delete-record">Yes, Delete It!</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <!--end modal --> 

                                        <!-- Modal -->
                                        <div class="modal fade zoomIn" id="editModal_{{$singldata->id}}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                                        id="btn-close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('admin.users.update',['user' => $singldata->post->user_id]) }}" method="post">
                                                        @csrf()
                                                          @method('PUT')
                                                        <div class="mt-1 text-center">
                                                            <div class="mt-2 pt-2 fs-15 mx-4 mx-sm-5">
                                                                <h4>Are you Sure ?</h4>
                                                                <p class="text-muted mx-4 mb-0">Are you Sure You want to Block this User?</p>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="status" value="block">
                                                        <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                                                            <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn w-sm btn-danger " id="delete-record">Yes, Block It!</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <!--end modal -->
                                    </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end">
                        {!! $alldata->appends(\Request::except('page'))->render() !!}
                    </div>
                </div>
            </div><!-- end card -->
        </div>
        <!-- end col -->
    </div>
    <!-- end col -->
</div>
            <!-- end row -->

<div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="exampleModalLabel">Add Report Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    id="close-modal"></button>
            </div>
            <form method="POST" action="{{route('admin.report-messages.store')}}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="customername-field"  class="form-label">Message</label>
                        <textarea name="message" class="form-control" placeholder="Please write here" required></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Add</button>
                        <!-- <button type="button" class="btn btn-success" id="edit-btn">Update</button> -->
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/prismjs/prismjs.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
