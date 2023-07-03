@extends('layouts.master')
@section('title')
    @lang('translation.profile')
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('assets/libs/swiper/swiper.min.css') }}">
@endsection
@section('content')
    {{--    <div class="profile-foreground position-relative mx-n4 mt-n4">--}}
    {{--        <div class="profile-wid-bg">--}}
    {{--            <img src="{{ URL::asset('assets/images/profile-bg.jpg') }}" alt="" class="profile-wid-img"/>--}}
    {{--        </div>--}}
    {{--    </div>--}}

    <div class="row">
        <div class="col-lg-12">
            <div>
                <div class="d-flex">
                    <!-- Nav tabs -->
                    <ul class="nav nav-pills animation-nav profile-nav gap-2 gap-lg-3 flex-grow-1"
                        role="tablist">
                        <li class="nav-item">
                            <a class="nav-link fs-14 active" data-bs-toggle="tab" href="#overview-tab"
                               role="tab">
                                <i class="ri-airplay-fill d-inline-block d-md-none"></i> <span
                                    class="d-none d-md-inline-block">Overview</span>
                            </a>
                        </li>


                    </ul>

                </div>
                <!-- Tab panes -->
                <div class="tab-content pt-4 text-muted">
                    <div class="tab-pane active" id="overview-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-xxl-3">
                                <!--end card-->
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Profile</h5>
                                        <div class="table-responsive">
                                            <table class="table table-borderless mb-0">
                                                <tbody>
                                                <tr>
                                                    <th class="ps-0" scope="row">Full Name :</th>
                                                    <td class="text-muted">{{$user->full_name}}</td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0" scope="row">Mobile :</th>
                                                    <td class="text-muted">({{$user->country_code}}
                                                        ) {{$user->phone_number ?? '' }}</td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0" scope="row">E-mail :</th>
                                                    <td class="text-muted">{{$user->email}}</td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0" scope="row">Location :</th>
                                                    <td class="text-muted">California, United States
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="ps-0" scope="row">Joining Date</th>
                                                    <td class="text-muted">{{$user->created_at->format('j F, Y')}}</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                                <!--end card-->
{{--                                @if($user->posts)--}}
{{--                                    <div class="card">--}}
{{--                                        <div class="card-body">--}}
{{--                                            <div class="d-flex align-items-center mb-4">--}}
{{--                                                <div class="flex-grow-1">--}}
{{--                                                    <h5 class="card-title mb-0">Popular Posts</h5>--}}
{{--                                                </div>--}}
{{--                                                <div class="flex-shrink-0">--}}
{{--                                                    <div class="dropdown">--}}
{{--                                                        <a href="#" role="button" id="dropdownMenuLink1"--}}
{{--                                                           data-bs-toggle="dropdown" aria-expanded="false">--}}
{{--                                                            <i class="ri-more-2-fill fs-14"></i>--}}
{{--                                                        </a>--}}

{{--                                                        <ul class="dropdown-menu dropdown-menu-end"--}}
{{--                                                            aria-labelledby="dropdownMenuLink1">--}}
{{--                                                            <li><a class="dropdown-item" href="#">View</a>--}}
{{--                                                            </li>--}}
{{--                                                            <li><a class="dropdown-item" href="#">Edit</a>--}}
{{--                                                            </li>--}}
{{--                                                            <li><a class="dropdown-item" href="#">Delete</a>--}}
{{--                                                            </li>--}}
{{--                                                        </ul>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}

{{--                                            @foreach($user->posts as $post)--}}
{{--                                                <div class="d-flex mb-4">--}}
{{--                                                    <div class="flex-shrink-0">--}}
{{--                                                        <img src="{{ URL::asset('assets/images/small/img-4.jpg') }}"--}}
{{--                                                             alt=""--}}
{{--                                                             height="50" class="rounded"/>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="flex-grow-1 ms-3 overflow-hidden">--}}
{{--                                                        <a href="javascript:void(0);">--}}
{{--                                                            <h6 class="text-truncate fs-14">{{$post->title ?? ''}}</h6>--}}
{{--                                                        </a>--}}
{{--                                                        <p class="text-muted mb-0">{{ isset($post->created_at)  ? $post->created_at->format('j F Y') : '' }}</p>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            @endforeach--}}
{{--                                        </div>--}}
{{--                                        <!--end card-body-->--}}
{{--                                    </div>--}}
{{--                                @endif--}}


                                @if($user->comments)
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-4">
                                                <div class="flex-grow-1">
                                                    <h5 class="card-title mb-0">Comments</h5>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <div class="dropdown">
                                                        <a href="#" role="button" id="dropdownMenuLink2"
                                                           data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="ri-more-2-fill fs-14"></i>
                                                        </a>

{{--                                                        <ul class="dropdown-menu dropdown-menu-end"--}}
{{--                                                            aria-labelledby="dropdownMenuLink2">--}}
{{--                                                            <li><a class="dropdown-item" href="#">View</a>--}}
{{--                                                            </li>--}}
{{--                                                            <li><a class="dropdown-item" href="#">Edit</a>--}}
{{--                                                            </li>--}}
{{--                                                            <li><a class="dropdown-item" href="#">Delete</a>--}}
{{--                                                            </li>--}}
{{--                                                        </ul>--}}
                                                    </div>
                                                </div>
                                            </div>
                                            <div>

                                                @foreach($user->comments as $comment)
                                                    <div class="d-flex align-items-center py-3">
                                                        <div class="avatar-xs flex-shrink-0 me-3">
                                                            <img
                                                                src="{{ URL::asset('assets/images/users/avatar-3.jpg') }}"
                                                                alt=""
                                                                class="img-fluid rounded-circle"/>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div>
                                                                <h5 class="fs-14 mb-1">{{$comment->user->username ?? ''}}</h5>
                                                                <p class="fs-13 text-muted mb-0">{{$comment->body ?? ''}}</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex-shrink-0 ms-2">
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-success"><i
                                                                    class="ri-user-add-line align-middle"></i></button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div><!-- end card body -->
                                    </div>
                                @endif
                            </div>

                            @if($user->stories)
                                <!--end col-->
                                <div class="col-xxl-9">
                                    <div class="card">
                                        <h3>Stories</h3>
                                        <div class="card-body">
                                            @foreach($user->stories as $store)
                                                <div class="d-flex mb-4">
                                                    <div class="flex-shrink-0">
                                                        <img src="{{ URL::asset('assets/images/small/img-4.jpg') }}"
                                                             alt=""
                                                             height="50" class="rounded"/>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3 overflow-hidden">
                                                        <a href="javascript:void(0);">
                                                            <h6 class="text-truncate fs-14">{{$store->media ?? ''}}</h6>
                                                        </a>

                                                        <p class="text-muted mb-0">{{ isset($store->description)  ? $store->description : '' }}</p>
                                                        <p class="text-muted mb-0">{{ isset($store->created_at)  ? $store->created_at->format('j F Y') : '' }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <!--end card-body-->
                                    </div><!-- end card -->

                                    @endif
                                    @if($user->posts)

                                    <div class="card">
                                        <h3>Popular Posts</h3>
                                        <div class="card-body">
                                            @foreach($user->posts as $post)
                                                @if($post->post_type='video')
                                                <h3>Video</h3>
                                                @foreach($post->getPostImages as $video)

                                                <div class="d-flex mb-4">
                                                    <div class="flex-shrink-0">

                                                        <video width="400" height="200" controls>
                                                            <source src="{{$video->path}}" type="video/mp4">
                                                            Vedio not exist.
                                                        </video>
                                                    </div>

                                                </div>
                                                @endforeach


                                                @elseif($post->post_type='audio')

                                                    <h3>Audio</h3>
                                                    @foreach($post->getPostImages as $audio)

                                                        <div class="d-flex mb-4">
                                                            <div class="flex-shrink-0">

                                                                <audio controls>
                                                                    <source src="{{$audio->path}}" type="audio/ogg">
                                                                    Audio not exist..
                                                                </audio>
                                                            </div>

                                                        </div>
                                                    @endforeach



                                                    @elseif($post->post_type='image')
                                                        <h3>Image</h3>
                                                        @foreach($post->getPostImages as $image)

                                                            <div class="d-flex mb-4">
                                                                <div class="flex-shrink-0">
                                                                    <a class="image-popup" href="{{$image->path}}" title="">
                                                                        <img class="avatar-lg me-2" alt="Image" src="{{$image->path}}" >
                                                                    </a>
                                                                </div>

                                                            </div>
                                                        @endforeach

                                                    @endif
                                                    @endforeach
                                        </div>
                                        <!--end card-body-->
                                    </div><!-- end card -->


                                </div>
                                <!--end col-->
                            @endif
                        </div>


                        </div>
                        <!--end row-->
                    </div>

                </div>
                <!--end tab-content-->
            </div>
        </div>
        <!--end col-->
    </div>
    <!--end row-->
@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/swiper/swiper.min.js') }}"></script>

    <script src="{{ URL::asset('assets/js/pages/profile.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
