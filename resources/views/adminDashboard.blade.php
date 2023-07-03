@extends('layouts.master')
@section('title') @lang('translation.analytics') @endsection
@section('css')

    <link href="{{ URL::asset('assets/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet">

@endsection
@section('content')

    @component('components.breadcrumb')
        @slot('li_1') Dashboards @endslot
        @slot('title') Analytics @endslot
    @endcomponent

    <div class="row">
        <div class="col-xxl-5">
            <div class="d-flex flex-column h-100">

                <div class="row">
                    <div class="col-md-3">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="fw-medium text-muted mb-0">Users</p>
                                        <h2 class="mt-4 ff-secondary fw-semibold"><span
                                                class="counter-value" data-target="{{$userCount}}">0</span></h2>
                                        <!-- <p class="mb-0 text-muted"><span
                                                class="badge bg-light text-success mb-0">
                                                <i class="ri-arrow-up-line align-middle"></i> 16.24 %
                                            </span> vs. previous month</p> -->
                                    </div>
                                    <div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-info rounded-circle fs-2">
                                                <i data-feather="users" class="text-info"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div> <!-- end card-->
                    </div> <!-- end col-->

                    <div class="col-md-3">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="fw-medium text-muted mb-0">Post</p>
                                        <h2 class="mt-4 ff-secondary fw-semibold"><span
                                                class="counter-value" data-target="{{$postCount}}">0</span></h2>
                                        <!-- <p class="mb-0 text-muted"><span
                                                class="badge bg-light text-danger mb-0">
                                                <i class="ri-arrow-down-line align-middle"></i> 3.96 %
                                            </span> vs. previous month</p> -->
                                    </div>
                                    <div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-info rounded-circle fs-2">
                                                <i data-feather="file" class="text-info"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div> <!-- end card-->
                    </div> 

                    <div class="col-md-3">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="fw-medium text-muted mb-0"><a href="{{route('admin.comments.index')}}">Comment</a></p>
                                        <h2 class="mt-4 ff-secondary fw-semibold"><span
                                                class="counter-value" data-target="{{$commentCount}}">0</span></h2>
                                        <!-- <p class="mb-0 text-muted"><span
                                                class="badge bg-light text-danger mb-0">
                                                <i class="ri-arrow-down-line align-middle"></i> 3.96 %
                                            </span> vs. previous month</p> -->
                                    </div>
                                    <div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-info rounded-circle fs-2">
                                                <i data-feather="message-square" class="text-info"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div> <!-- end card-->
                    </div>

                    <div class="col-md-3">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="fw-medium text-muted mb-0"><a href="{{route('admin.likes.index')}}">Likes</a></p>
                                        <h2 class="mt-4 ff-secondary fw-semibold"><span
                                                class="counter-value" data-target="{{$likeCount}}">0</span></h2>
                                        <!-- <p class="mb-0 text-muted"><span
                                                class="badge bg-light text-danger mb-0">
                                                <i class="ri-arrow-down-line align-middle"></i> 3.96 %
                                            </span> vs. previous month</p> -->
                                    </div>
                                    <div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-info rounded-circle fs-2">
                                                <i data-feather="thumbs-up" class="text-info"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div> <!-- end card-->
                    </div>
                </div> <!-- end row-->

            </div>
        </div> <!-- end col-->

       
    </div> <!-- end row-->


    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Monthwise Details</h4>
                </div>
                <div class="card-body">
                    <canvas id="lineChart" class="chartjs-chart"
                        data-colors='["--vz-primary-rgb, 0.2", "--vz-primary", "--vz-success-rgb, 0.2", "--vz-success"]'></canvas>
                </div>
            </div>
        </div> <!-- end col -->

        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Monthwise</h4>
                </div>
                <div class="card-body">
                    <canvas id="bar" class="chartjs-chart"
                        data-colors='["--vz-primary-rgb, 0.8", "--vz-primary-rgb, 0.9"]'></canvas>

                </div>
            </div>
        </div> <!-- end col -->
    </div> <!-- end row -->

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Summary Chart</h4>
                </div>
                <div class="card-body">
                    <canvas id="pieChart" class="chartjs-chart" data-colors='["--vz-success", "--vz-light"]'></canvas>
                </div>
            </div>
        </div> <!-- end col -->

        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Summary Chart</h4>
                </div>
                <div class="card-body">
                    <canvas id="doughnut" class="chartjs-chart" data-colors='["--vz-primary", "--vz-light"]'></canvas>
                </div>
            </div>
        </div> <!-- end col -->
    </div> <!-- end row -->

@endsection
@section('script')
    
    <script src="{{ URL::asset('assets/libs/chart.js/chart.js.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/chartjs.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
