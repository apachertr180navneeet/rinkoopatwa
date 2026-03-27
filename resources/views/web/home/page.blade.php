@extends('web.layouts.app')
@section('style')
<style>
    .container ul{
        list-style-type: disc;
    }
    .page-content p{
        line-height: 30px;
    }
</style>
@endsection 

@section('content')

<section class="page-content">
    <div class="container">
            <div class="col-xl-12 col-lg-12 col-md-12">
                <h3>{!! $page->name !!}</h3>
            </div>
            <br>
            <div class="col-xl-12 col-lg-12 col-md-12">
                {!! $page->value !!}
            </div>
        </div>
    </div>
</section>
@endsection 

@section('script')
@endsection
