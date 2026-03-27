<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <title>{{ config('app.name') }}</title>
        <link rel="shortcut icon" type="image/x-icon" href="{{asset('assets/web/img/favicon.png')}}">
        <link href="{{asset('assets/web/css/styles.css?v=1.1')}}" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        @yield('style')
    </head>

    <body>
        <div class="preloader"></div>
        <div id="main-wrapper">
            @include('web.layouts.elements.header')
            @yield('content')
            @include('web.layouts.elements.footer')
        </div>

        <script src="{{asset('assets/web/js/jquery.min.js')}}"></script> 
        <script src="{{asset('assets/web/js/bootstrap.min.js')}}"></script> 
        @yield('script')
    </body>
</html>
