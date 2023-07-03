@extends('layouts.master')
@section('title')
    @lang('View Post Details') 
@endsection

@section('css')

<link rel="stylesheet" href="{{ URL::asset('assets/libs/glightbox/glightbox.min.css') }}">

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
    @slot('title') View Post Details @endslot
@endcomponent

@include('layouts.messages')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0"> @yield('title')</h4>
            </div><!-- end card header -->

            <div class="card-body">
                    <div class="row g-4">
                        <div class="col-sm-4">
                                <div class="mb-3 fs-15">
                                    <label for="id-field" class="form-label">Post By:</label>
                                    <span class="form-label ml-3"><u>{{ucfirst($post->user->full_name) ?? ""}}</u></span>
                                </div>
                        </div>
                        <div class="col-sm-4">
                                <div class="mb-3 fs-15">
                                    <label for="id-field" class="form-label">Post Type:</label>
                                    <span class="form-label ml-3"><u>{{ucfirst($post->type) ?? ""}}</u></span>
                                </div>
                        </div>
                        <div class="col-sm-4">
                                <div class="mb-3 fs-15">
                                    <label for="id-field" class="form-label">Post Date:</label>
                                    <span class="form-label ml-3"><u>{{globaldate($post->created_at) ?? ""}}</u></span>
                                </div>
                        </div>
                    </div>
                        <div class="col-sm-12">
                                <div class="mb-3 fs-15">
                                    <label for="id-field" class="form-label">Body:</label>
                                    <u><span class="form-label ml-3">{{ucfirst($post->body) ?? ""}}</span></u>
                                </div>
                        </div>
                    
            </div><!-- end card -->
        </div>
        <!-- end col -->
    </div>
    <!-- end col -->
</div>
@if(in_array($post->type,['video','audio','image']))
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0"> Post Content</h4>
            </div><!-- end card header -->

            <div class="card-body">
                @if($post->type=='video')
                    <div class="row g-4">
                        <div class="col-sm-4">
                                <div class="mb-3 fs-15">
                                    <video width="400" height="200" controls>
                                      <source src="{{$post->getPostImages[0]->path}}" type="video/mp4">
                                      Vedio not exist.
                                    </video>
                                </div>
                        </div>
                    </div>
                @endif
                @if($post->type=='audio')
                    <div class="row g-4">
                        <div class="col-sm-4">
                                <div class="mb-3 fs-15">
                                    <audio controls>
                                      <source src="{{$post->getPostImages[0]->path}}" type="audio/ogg">
                                         Audio not exist..
                                    </audio>
                                </div>
                        </div>
                    </div>
                @endif
                @if($post->type=='image')
                    <div class="row g-4">
                        @foreach($post->getPostImages as $singlepath)
                        <div class="col-md-2">
                            <a class="image-popup" href="{{$singlepath->path}}" title=""> 
                            <img class="avatar-lg me-2" alt="Image" src="{{$singlepath->path}}" data-holder-rendered="true">
                                    </a>
                        </div>
                        @endforeach
                    </div>
                @endif
                    
            </div><!-- end card -->
        </div>
        <!-- end col -->
    </div>
    <!-- end col -->
</div>
@endif

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Abuse Report Details</h4>
                <div class="accordion" id="accordionExample">
                    @foreach($aposts as $apost)
                    <div class="accordion-item border rounded mt-2">
                      <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo_{{$loop->iteration}}" aria-expanded="false" aria-controls="collapseTwo">
                            Report {{$loop->iteration}}
                        </button>
                      </h2>
                      <div id="collapseTwo_{{$loop->iteration}}" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <div class="row g-4">
                                <div class="col-sm-4">
                                        <div class="mb-3 fs-15">
                                            <label for="id-field" class="form-label">Report By:</label>
                                            <span class="form-label ml-3"><u>{{ucfirst($apost->user->full_name) ?? ""}}</u></span>
                                        </div>
                                </div>
                                <div class="col-sm-4">
                                        <div class="mb-3 fs-15">
                                            <label for="id-field" class="form-label">Report Date:</label>
                                            <span class="form-label ml-3"><u>{{globaldate($apost->created_at) ?? ""}}</u></span>
                                        </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                    <div class="mb-3 fs-15">
                                        <label for="id-field" class="form-label">Report Abuse Reason:</label>
                                        <u><span class="form-label ml-3">{{$apost->reason ?? ""}}</span></u>
                                    </div>
                            </div>
                        </div>
                      </div>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/prismjs/prismjs.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/libs/glightbox/glightbox.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/libs/isotope-layout/isotope-layout.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/pages/gallery.init.js') }}"></script>
@endsection
