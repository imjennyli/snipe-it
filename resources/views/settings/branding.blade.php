@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Update Branding Settings
    @parent
@stop

@section('header_right')
    <a href="{{ route('settings.index') }}" class="btn btn-default"> {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')

    <style>
        .checkbox label {
            padding-right: 40px;
        }
    </style>
    <!-- Bootstrap Color Picker -->
    <link rel="stylesheet" href="{{ asset('js/plugins/colorpicker/bootstrap-colorpicker.min.css') }}">


    {{ Form::open(['method' => 'POST', 'files' => true, 'class' => 'form-horizontal', 'role' => 'form' ]) }}
    <!-- CSRF Token -->
    {{csrf_field()}}

    <div class="row">
        <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">


            <div class="panel box box-default">
                <div class="box-header with-border">
                    <h4 class="box-title">
                        <i class="fa fa-copyright"></i> Branding
                    </h4>
                </div>
                <div class="box-body">


                    <div class="col-md-11 col-md-offset-1">

                        <!-- Site name -->
                        <div class="form-group {{ $errors->has('site_name') ? 'error' : '' }}">

                            <div class="col-md-3">
                                {{ Form::label('site_name', trans('admin/settings/general.site_name')) }}
                            </div>
                            <div class="col-md-7">
                                @if (config('app.lock_passwords')===true)
                                    {{ Form::text('site_name', Input::old('site_name', $setting->site_name), array('class' => 'form-control', 'disabled'=>'disabled','placeholder' => 'Snipe-IT Asset Management')) }}
                                @else
                                    {{ Form::text('site_name',
                                        Input::old('site_name', $setting->site_name), array('class' => 'form-control','placeholder' => 'Snipe-IT Asset Management')) }}
                                @endif
                                {!! $errors->first('site_name', '<span class="alert-msg">:message</span>') !!}
                            </div>
                        </div>


                        <!-- Logo -->
                        <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                            <div class="col-md-3">
                                {{ Form::label('logo', trans('admin/settings/general.logo')) }}
                            </div>
                            <div class="col-md-9">
                                @if (config('app.lock_passwords'))
                                    <p class="help-block">{{ trans('general.lock_passwords') }}</p>
                                @else
                                    {{ Form::file('image') }}
                                    {!! $errors->first('image', '<span class="alert-msg">:message</span>') !!}
                                    {{ Form::checkbox('clear_logo', '1', Input::old('clear_logo'),array('class' => 'minimal')) }} Remove
                                @endif
                            </div>
                        </div>

                        <!-- Branding -->
                        <div class="form-group {{ $errors->has('brand') ? 'error' : '' }}">
                            <div class="col-md-3">
                                {{ Form::label('brand', trans('admin/settings/general.brand')) }}
                            </div>
                            <div class="col-md-9">
                                {!! Form::select('brand', array('1'=>'Text','2'=>'Logo','3'=>'Logo + Text'), Input::old('brand', $setting->brand), array('class' => 'form-control', 'style'=>'width: 150px ;')) !!}
                                {!! $errors->first('brand', '<span class="alert-msg">:message</span>') !!}
                            </div>
                        </div>

                        <!-- Header color -->
                        <div class="form-group {{ $errors->has('header_color') ? 'error' : '' }}">
                            <div class="col-md-3">
                                {{ Form::label('header_color', trans('admin/settings/general.header_color')) }}
                            </div>
                            <div class="col-md-2">
                                <div class="input-group header-color">
                                    {{ Form::text('header_color', Input::old('header_color', $setting->header_color), array('class' => 'form-control', 'style' => 'width: 100px;','placeholder' => '#FF0000')) }}
                                    <div class="input-group-addon">
                                        <i></i>
                                    </div>
                                </div><!-- /.input group -->
                                {!! $errors->first('header_color', '<span class="alert-msg">:message</span>') !!}
                            </div>
                        </div>

                        <!-- Custom css -->
                        <div class="form-group {{ $errors->has('custom_css') ? 'error' : '' }}">
                            <div class="col-md-3">
                                {{ Form::label('custom_css', trans('admin/settings/general.custom_css')) }}
                            </div>
                            <div class="col-md-9">
                                @if (config('app.lock_passwords')===true)
                                    {{ Form::textarea('custom_css', Input::old('custom_css', $setting->custom_css), array('class' => 'form-control','placeholder' => 'Add your custom CSS','disabled'=>'disabled')) }}
                                    {!! $errors->first('custom_css', '<span class="alert-msg">:message</span>') !!}
                                    <p class="help-block">{{ trans('general.lock_passwords') }}</p>
                                @else
                                    {{ Form::textarea('custom_css', Input::old('custom_css', $setting->custom_css), array('class' => 'form-control','placeholder' => 'Add your custom CSS')) }}
                                    {!! $errors->first('custom_css', '<span class="alert-msg">:message</span>') !!}
                                @endif
                                <p class="help-block">{{ trans('admin/settings/general.custom_css_help') }}</p>
                            </div>
                        </div>




                    </div>

                    </div> <!--/.box-body-->
                    <div class="box-footer">
                        <div class="text-left col-md-6">
                            <a class="btn btn-link text-left" href="{{ route('settings.index') }}">{{ trans('button.cancel') }}</a>
                        </div>
                        <div class="text-right col-md-6">
                            <button type="submit" class="btn btn-success"><i class="fa fa-check icon-white"></i> {{ trans('general.save') }}</button>
                        </div>

                    </div>
            </div> <!-- /box -->
        </div> <!-- /.col-md-8-->
    </div> <!-- /.row-->

    {{Form::close()}}

@stop

@section('moar_scripts')
    <!-- bootstrap color picker -->
    <script nonce="{{ csrf_token() }}">
        //color picker with addon
        $(".header-color").colorpicker();
        // toggle the disabled state of asset id prefix
        $('#auto_increment_assets').on('ifChecked', function(){
            $('#auto_increment_prefix').prop('disabled', false).focus();
        }).on('ifUnchecked', function(){
            $('#auto_increment_prefix').prop('disabled', true);
        });
    </script>
@stop
