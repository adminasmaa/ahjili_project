@extends('layouts.master')
@section('title')
    @lang('translation.add')
@endsection

@section('content')
    <form method="POST" action="{{route('admin.roles.store')}}">
        @csrf
        @include('partials._errors')

        <h3>@lang('translation.add')</h3>

        <div class="row">


            <div class="col-lg-4">

                <label>@lang('translation.name')</label>
                <input type="text" name="name" class="form-control" required>


            </div>
            <div class="col-lg-4">

                <label>@lang('translation.display_name')</label>
                <input type="text" name="display_name" class="form-control">


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


        <div class="row">

            <div class="col-lg-11">
            <ul class="nav ">

                <h3>Permission</h3>
                <table class="table table-hover table-bordered">

                    @foreach ($models as $index=>$model)
                        <tr>
                            <td>
                                <li class="form-group {{ $index == 0 ? 'active' : '' }}">
                                    @lang('site.' . $model)</li>
                            </td>
                            <td>

                                <div
                                    class="animate-chk d-flex justify-content-around form-group {{ $index == 0 ? 'active' : '' }}"
                                    id="{{ $model }}">

                                    @foreach ($maps as $map)
                                        <label>
                                            <input class="checkbox_animated"
                                                   type="checkbox"
                                                   name="permissions[]"
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


        </div><!-- end of nav tabs -->

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

