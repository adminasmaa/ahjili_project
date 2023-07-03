@extends('layouts.master')
@section('title')
    @lang('translation.changePassword')
@endsection

@section('content')
    <form method="POST" action="{{route('admin.updatePassword')}}">
        @csrf

        <div class="row">
            @include('partials._errors')


            <h3>@lang('translation.changePassword')</h3>
            <div class="col-lg-6">
                <input type="hidden" name="user" value="{{$user}}"
                <input type="hidden" name="password" value="{{$password}}"
                <label>@lang('translation.oldpassword')</label>
                <input type="password" name="oldpassword" class="form-control" required>


            </div>
        </div>

        <div class="row">


            <div class="col-lg-6">

                <label>@lang('translation.newpassword')</label>
                <input type="password" name="newpassword" class="form-control" required>


            </div>

        </div>
        <div class="row">


            <div class="col-lg-6">

                <label>@lang('translation.confirmpassword')</label>
                <input type="password" name="confirmpassword" class="form-control" required>


            </div>

        </div>
        <br>
        <div class="row">
            <br>

            <div class="col-lg-2">
                <button type="submit" class="btn btn-danger">@lang('translation.send') </button>
            </div>
        </div>
        <!--end col-->
    </form>
    <!--end row-->
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            // $(".alert").delay(5000).slideUp(300);
            $(".alert").slideDown(300).delay(5000).slideUp(300);
        });
        setTimeout(function () {
            $('.alert-box').remove();
        }, 30000);
    </script>
@endsection
