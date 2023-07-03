@extends('layouts.master')
@section('title')
    @lang('translation.edit')
@endsection

@section('content')
    <form  method="post" action="{{route('admin.usermangements.update',$user->id)}}">
        {{ csrf_field() }}
        {{ method_field('put') }}
        @include('partials._errors')

        <h3>@lang('translation.edit')</h3>

        <div class="row">


            <div class="col-lg-4">

                <label>@lang('translation.username')</label>
                <input type="text" name="username" class="form-control" required value="{{$user->username}}">


            </div>
            <div class="col-lg-4">

                <label>@lang('translation.job')</label>
                <input type="text" name="job" class="form-control" value="{{$user->job}}">


            </div>

        </div>

        <div class="row">


            <div class="col-lg-4">

                <label>@lang('translation.email')</label>
                <input type="email" name="email" class="form-control" required value="{{$user->email}}">


            </div>

            <div class="col-lg-4">

                <label>@lang('translation.roles')</label>
                <select name="roles[]" class="form-control">
                    <option>Select Please</option>
                    @foreach($roles as $role)

                        <option value="{{$role->id}}"  {{ $user->hasRole($role->name) ? 'selected' : '' }}>
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
               {{$user->description ?? ''}}
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

