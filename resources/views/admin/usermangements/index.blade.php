@extends('layouts.master')
@section('title')
    @lang('Usermangements')
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

        .td-padding {
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
            UserManagements
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Usermangements List</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="customerList">
                        <div class="row g-4 mb-3">
                            <div class="col-sm-auto">

                                <div>
                                    <a href="{{route('admin.usermangements.create')}}"
                                       class="btn btn-success add-btn"><i
                                            class="ri-add-line align-bottom me-1"></i> @lang('translation.add') @endlang
                                    </a>
                                </div>

                            </div>
                            <div class="col-sm">
                                <div class="d-flex justify-content-sm-end">
                                    <div class="search-box ms-2">
                                        <form name="user-search" method="get"
                                              action="{{route('admin.usermangements.index')}}">
                                            <label>
                                                <input value="{{request()->has('search') ? request()->search : ''}}"
                                                       type="text" name="search" id="search" class="form-control search"
                                                       placeholder="Search...">
                                                <i class="ri-search-line search-icon"></i>
                                                <button id="search-button" type="submit" class="btn btn-success mb-1">
                                                    Search
                                                </button>
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
                                    <th class="sort" data-sort="customer_name">Name</th>
                                    <th class="sort" data-sort="email">Email</th>
                                    <th class="sort" data-sort="phone">job</th>
                                    <th class="sort" data-sort="date">Joining Date</th>
                                    <th class="sort" data-sort="status">Status</th>
                                    <th class="sort" data-sort="action">Action</th>
                                </tr>
                                </thead>
                                <tbody class="list form-check-all">
                                @foreach($users as $user)
                                    <tr>
                                        <td class="id" style="display:none;"><a href="javascript:void(0);"
                                                                                class="fw-medium link-primary ">{{$user->id}}</a>
                                        </td>
                                        <td class="customer_name td-padding">{{$user->username}}</td>
                                        <td class="email">{{$user->email}}</td>
                                        <td class="phone">{{$user->job}}</td>
                                        <td class="date">{{ globaldate($user->created_at) }}</td>

                                        <td class="status">
                                            @if($user->active_status == "1")
                                                <span class="badge badge-soft-success text-uppercase">Active</span>
                                            @elseif($user->active_status == "0")
                                                <span class="badge badge-soft-warning text-uppercase">In-Active</span>
                                            @endif
                                        </td>



                                        <td>
                                            <div class="d-flex gap-2">
                                                <div class="edit_view" data-bs-toggle="tooltip" data-bs-placement="top"
                                                     title="View Edit">
                                                    <a href="{{route('admin.usermangements.edit',$user->id)}}"
                                                       class="btn btn-sm  btn-primary btn-icon waves-effect waves-light"><i
                                                            class="mdi mdi-email-edit"></i></a>
                                                </div>
                                                <div class="eye_view" data-bs-toggle="tooltip" data-bs-placement="top"
                                                     title="Change Password">
                                                    <a href="{{route('admin.ChangePassword', ['id' => $user->id])}}"
                                                       class="btn btn-sm btn-info remove-item-btn"><i
                                                            class="lab la-expeditedssl"></i></a>
                                                </div>
                                                <div class="remove" data-bs-toggle="tooltip" data-bs-placement="top"
                                                     title="Delete">
                                                    <button class="btn btn-sm btn-danger remove-item-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteRecordModal_{{$user->id}}"><i
                                                            class="mdi mdi-delete"></i></button>
                                                </div>

                                                <!-- Modal -->
                                                <div class="modal fade zoomIn" id="deleteRecordModal_{{$user->id}}"
                                                     tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal" aria-label="Close"
                                                                        id="btn-close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form
                                                                    action="{{route('admin.users.destroy',$user->id)}}"
                                                                    method="post">
                                                                    @method('delete')
                                                                    @csrf()
                                                                    <div class="mt-2 text-center">
                                                                        <lord-icon
                                                                            src="https://cdn.lordicon.com/gsqxdxog.json"
                                                                            trigger="loop"
                                                                            colors="primary:#f7b84b,secondary:#f06548"
                                                                            style="width:100px;height:100px"></lord-icon>
                                                                        <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                                                                            <h4>Are you Sure ?</h4>
                                                                            <p class="text-muted mx-4 mb-0">Are you Sure
                                                                                You want to Remove this Record ?</p>
                                                                        </div>
                                                                    </div>
                                                                    <div
                                                                        class="d-flex gap-2 justify-content-center mt-4 mb-2">
                                                                        <button type="button" class="btn w-sm btn-light"
                                                                                data-bs-dismiss="modal">Close
                                                                        </button>
                                                                        <button type="submit"
                                                                                class="btn w-sm btn-danger "
                                                                                id="delete-record">Yes, Delete It!
                                                                        </button>
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
                            {!! $users->appends(\Request::except('page'))->render() !!}
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
