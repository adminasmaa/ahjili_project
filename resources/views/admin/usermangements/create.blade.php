@extends('layouts.master')
@section('title')
    @lang('translation.add')
@endsection

@section('content')
    <form method="POST" action="{{route('admin.usermangements.store')}}">
        @csrf
        @include('partials._errors')

        <h3>@lang('translation.add')</h3>

        <div class="row">


            <div class="col-lg-4">

                <label>@lang('translation.username')</label>
                <input type="text" name="username" class="form-control" required>


            </div>
            <div class="col-lg-4">

                <label>@lang('translation.job')</label>
                <input type="text" name="job" class="form-control">


            </div>

        </div>

        <div class="row">


            <div class="col-lg-4">

                <label>@lang('translation.email')</label>
                <input type="email" name="email" class="form-control" required>


            </div>

            <div class="col-lg-4">

                <label>@lang('translation.roles')</label>
                <select name="roles[]" class="form-control">
                    <option>Select Please</option>
                    @foreach($roles as $role)

                        <option value="{{$role->id}}">
                            {{$role->name ?? ''}}
                        </option>

                    @endforeach


                </select>

            </div>

        </div>


        <div class="row">


            <div class="col-lg-4">

                <label>@lang('translation.password')</label>
                <input type="password" name="password" class="form-control" required>


            </div>

        </div>


        <div class="row">


            <div class="col-lg-4">

                <label>@lang('translation.description')</label>
                <textarea class="form-control" cols="5" rows="5"
                          name="description">

                                        </textarea>


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

