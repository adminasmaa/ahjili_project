@extends('layouts.master')
@section('title')
    @lang('translation.edit')
@endsection

@section('content')
    <form  method="post" action="{{route('admin.roles.update',$role->id)}}">
        {{ csrf_field() }}
        {{ method_field('put') }}
        @include('partials._errors')

        <h3>@lang('translation.edit')</h3>

        <div class="row">


            <div class="col-lg-4">

                <label>@lang('translation.name')</label>
                <input type="text" name="name" class="form-control" required value="{{$role->name}}">


            </div>
            <div class="col-lg-4">

                <label>@lang('translation.display_name')</label>
                <input type="text" name="display_name" class="form-control" value="{{$role->display_name}}">


            </div>

        </div>






        <div class="row">


            <div class="col-lg-4">

                <label>@lang('translation.description')</label>
                <textarea class="form-control" cols="5" rows="5"
                          name="description">
               {{$role->description ?? ''}}
                                        </textarea>


            </div>

        </div>


        <div class="row">
<h3>Permission</h3>

            <div class="col-lg-11">

            <ul class="nav ">
                <table class="table table-hover table-bordered">


                    @foreach ($models as $index=>$model)
                        <tr>
                            <td>
                                <li
                                    class="form-group {{ $index == 0 ? 'active' : '' }}">
                                    @lang('site.' . $model)</li>
                            </td>
                            <td>

                                <div
                                    class="animate-chk d-flex justify-content-around form-group {{ $index == 0 ? 'active' : '' }}"
                                    id="{{ $model }}">

                                    @foreach ($maps as $map)
                                        <label><input class="checkbox_animated"
                                                      type="checkbox"
                                                      name="permissions[]"
                                                      {{ $role->hasPermission($map . '_' . $model) ? 'checked' : '' }}
                                                      value="{{ $map . '_' . $model }}">
                                            @lang('site.'
                                            . $map)
                                            <span></span>
                                        </label>

                                    @endforeach

                                </div>
                            </td>

                        </tr>
                    @endforeach
                </table>
            </ul>
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

