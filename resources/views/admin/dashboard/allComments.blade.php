@extends('layouts.master')
        @section('title') @lang('All Comments') @endsection
        @section('content')
            @component('components.breadcrumb')
                @slot('li_1') Dashboard @endslot
                @slot('title') All Comments @endslot
            @endcomponent
                <!-- end row -->

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Comment</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <p class="text-muted">All comments in the application</p>

                    <div id="pagination-list">
                        <!-- <div class="mb-2">
                            <input class="search form-control" placeholder="Search" />
                        </div> -->

                        <div class="mx-n3">
                            <ul class="list list-group list-group-flush mb-0">
                        @if(count($comments) > 0)
                            @foreach($comments as $comment)
                                <li class="list-group-item">
                                    <div class="d-flex align-items-center pagi-list">
                                        <div class="flex-shrink-0 me-3">
                                            <div>
                                                <img class="image avatar-xs rounded-circle" alt=""
                                                    src="{{ URL::asset('assets/images/users/avatar-1.jpg') }}">
                                            </div>
                                        </div>

                                        <div class="flex-grow-1 overflow-hidden">
                                            <h5 class="fs-13 mb-1"><a href="#" class="link text-dark">{{$comment->user->username}}</a></h5>
                                            <p class="born timestamp text-muted mb-0">{{$comment->body}}</p>
                                        </div>

                                        <!-- <div class="flex-shrink-0 ms-2">
                                            <div>
                                                <button type="button" class="btn btn-sm btn-light"><i
                                                        class="ri-mail-line align-bottom"></i> Message</button>
                                            </div>
                                        </div> -->
                                    </div>
                                </li>
                            @endforeach
                        @else
                        <h5>No record found</h5>
                        @endif
                            </ul>
                            <!-- end ul list -->

                            <div class="d-flex justify-content-center">
                                <div class="pagination-wrap hstack gap-2">
                                   {{$comments->links()}}
                                </div>
                            </div>

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
    <script src="{{ URL::asset('assets/libs/list.js/list.js.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/list.pagination.js/list.pagination.js.min.js') }}"></script>

    <!-- listjs init -->
    <script src="{{ URL::asset('assets/js/pages/listjs.init.js') }}"></script>

    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
        @endsection
