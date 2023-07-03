@extends('layouts.master')
@section('title')
    @lang('Notifications')
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
            Notifications
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Notifications List</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="customerList">
                        <div class="row g-4 mb-3">
                            <div class="col-sm-auto">
                                <div>
                                    <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal"
                                            id="create-btn" data-bs-target="#showModal"><i
                                            class="ri-add-line align-bottom me-1"></i> Add Notifications
                                    </button>
                                </div>
                            </div>
                            <div class="col-sm">
                                <div class="d-flex justify-content-sm-end">
                                    <div class="search-box ms-2">
                                        <form name="user-search" method="get" action="{{route('admin.notifications.index')}}">
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
                                    <th scope="col" style="width: 50px;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="checkAll"
                                                   value="option">
                                        </div>
                                    </th>
                                    <th class="sort" data-sort="customer_name">Name</th>
                                    <th class="sort" data-sort="customer_name">Title</th>
                                    <th class="sort" data-sort="status">Description</th>

                                    <th class="sort" data-sort="date"> Date</th>
                                    <th class="sort" data-sort="action">Action</th>
                                </tr>
                                </thead>
                                <tbody class="list form-check-all">
                                @foreach($notifications as $notification)
                                    <tr>
                                        <th scope="row">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="checkAll"
                                                       value="option1">
                                            </div>
                                        </th>
                                        <td class="id" style="display:none;"><a href="javascript:void(0);"
                                                                                class="fw-medium link-primary">{{$notification->id}}</a>
                                        </td>
                                        <td class="username">{{$notification->user->username  ?? ""}}</td>
                                        <td class="username">{{$notification->title ??''}}</td>

                                        <td class="customer_name">{{Str::limit($notification->description,50)}}</td>
                                        <td class="date">{{ isset($notification->created_at)  ? $notification->created_at->format('j F Y') : '' }}</td>


                                        <td>
                                            <div class="d-flex gap-2">
                                                <div class="edit" data-bs-toggle="tooltip" data-bs-placement="top"
                                                     title="Edit">
                                                    <button class="btn btn-sm btn-success edit-item-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editModal_{{$notification->id}}"><i
                                                            class="mdi mdi-human-edit"></i></button>
                                                </div>

                                                <div class="remove" data-bs-toggle="tooltip" data-bs-placement="top"
                                                     title="Delete">
                                                    <button class="btn btn-sm btn-danger remove-item-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteRecordModal"><i
                                                            class="mdi mdi-delete"></i></button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal -->
                                    <div class="modal fade zoomIn" id="deleteRecordModal" tabindex="-1"
                                         aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"
                                                            id="btn-close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form
                                                        action="{{route('admin.notifications.destroy',$notification->id)}}"
                                                        method="post">
                                                        @method('delete')
                                                        @csrf()
                                                        <div class="mt-2 text-center">
                                                            <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json"
                                                                       trigger="loop"
                                                                       colors="primary:#f7b84b,secondary:#f06548"
                                                                       style="width:100px;height:100px"></lord-icon>
                                                            <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                                                                <h4>Are you Sure ?</h4>
                                                                <p class="text-muted mx-4 mb-0">Are you Sure You want to
                                                                    Remove this Record ?</p>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                                                            <button type="button" class="btn w-sm btn-light"
                                                                    data-bs-dismiss="modal">Close
                                                            </button>
                                                            <button type="submit" class="btn w-sm btn-danger "
                                                                    id="delete-record">Yes, Delete It!
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end modal -->
                                    {{--EditModal--}}
                                    <div class="modal fade" id="editModal_{{$notification->id}}" tabindex="-1"
                                         aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-light p-3">
                                                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"
                                                            id="close-modal"></button>
                                                </div>
                                                <form
                                                    action="{{ route('admin.notifications.update',$notification->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">

                                                        <div class="mb-3" id="modal-id" style="display: none;">
                                                            <label for="id-field" class="form-label">ID</label>
                                                            <input type="text" id="id-field" class="form-control"
                                                                   placeholder="ID" readonly/>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="customername-field"
                                                                   class="form-label">Title</label>
                                                            <input type="text" id="customername-field"
                                                                   class="form-control" placeholder="Title" name="title"
                                                                   value="{{$notification->title}}" required/>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="customername-field"
                                                                   class="form-label">Description</label>
                                                            <textarea class="form-control" placeholder="Description"
                                                                      name="data" required rows="4">
                                     {{$notification->description ?? ''}}
                            </textarea>
                                                        </div>


                                                        <div>
                                                            <label for="status-field" class="form-label">User</label>
                                                            <select class="form-control" data-trigger name="user_id"
                                                                    id="status-field">
                                                                <option >@lang('site.select')</option>
                                                                @foreach($users as $user)
                                                                    <option value="{{$user->id}}"  @if($notification->notifiable_id==$user->id) selected @endif >{{$user->username}}</option>

                                                                @endforeach

                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <div class="hstack gap-2 justify-content-end">
                                                            <button type="button" class="btn btn-light"
                                                                    data-bs-dismiss="modal">Close
                                                            </button>
                                                            <button type="submit" class="btn btn-success" id="add-btn">
                                                                Update Notification
                                                            </button>
                                                            <!-- <button type="button" class="btn btn-success" id="edit-btn">Update</button> -->
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end modal -->

                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end">
                            {!! $notifications->appends(\Request::except('page'))->render() !!}
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
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            id="close-modal"></button>
                </div>
                <form action="{{ route('admin.notifications.store') }}"
                      method="POST">
                    @csrf
                    <div class="modal-body">

                        <div class="mb-3" id="modal-id" style="display: none;">
                            <label for="id-field" class="form-label">ID</label>
                            <input type="text" id="id-field" class="form-control" placeholder="ID" readonly/>
                        </div>

                        <div class="mb-3">
                            <label for="customername-field" class="form-label">Title</label>
                            <input type="text" id="customername-field" class="form-control" placeholder="Title"
                                   name="title"
                                   required/>
                        </div>

                        <div class="mb-3">
                            <label for="customername-field" class="form-label">Description</label>


                            <textarea class="form-control" placeholder="Description" name="data" required rows="4">

                            </textarea>
                        </div>


                        <div>
                            <label for="status-field" class="form-label">User</label>
                            <select class="form-control" data-trigger name="user_id" id="status-field">
                                <option>@lang('site.select') @endlang</option>
                                @foreach($users as $user)
                                    <option value="{{$user->id}}">{{$user->username}}</option>

                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success" id="add-btn">Add Notification</button>
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
