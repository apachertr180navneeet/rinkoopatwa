@extends('admin.layouts.login_layout') 

@section('content') 

<section class="breadcrumb-area bread-bg-9">
    <div class="breadcrumb-wrap">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="breadcrumb-content">
                        <div class="section-heading">
                            <h2 class="sec__title text-white"></h2>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="breadcrumb-list gs-text-bread">
                        <ul class="list-items">
                            <li><a href="/">Home</a></li>
                            <li></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</section>

<section class="contact-area section--padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
              
            </div>
            <div class="col-lg-6">
                <div class="form-box">
                    <div class="form-title-wrap">
                        <h3 class="title">Register</h3>
                        <p class="font-size-15 mb-0">Hello! Welcome Create a New Account</p>
                    </div>
                    <div class="form-content ">
                        <div class="contact-form-action">
                           <form method="POST" action="{{ route('admin.register') }}">
                              @csrf
                              <div class="input-box">
                                 <label class="label-text">Store Name</label>
                                 <div class="form-group">
                                    <span class="la la-store form-icon"></span>
                                    <input class="form-control{{ $errors->has('store_name') ? ' is-invalid' : '' }}" name="store_name" value="{{ old('store_name') }}" required autofocus type="text" placeholder="Type your store name">
                                    @if ($errors->has('store_name'))
                                       <span class="invalid-feedback">
                                          <strong>{{ $errors->first('store_name') }}</strong>
                                       </span>
                                    @endif
                                 </div>
                              </div>
                               <div class="input-box">
                                  <label class="label-text">Username</label>
                                  <div class="form-group">
                                     <span class="la la-user form-icon"></span>
                                     <input class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value="{{ old('username') }}" required type="text" placeholder="Type your username">
                                    @if ($errors->has('username'))
                                       <span class="invalid-feedback">
                                          <strong>{{ $errors->first('username') }}</strong>
                                       </span>
                                    @endif
                                  </div>
                               </div>
                              
                               <div class="input-box">
                                  <label class="label-text">Email Address</label>
                                  <div class="form-group">
                                     <span class="la la-envelope form-icon"></span>
                                     <input class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required type="email" placeholder="Type your email">
                                    @if ($errors->has('email'))
                                       <span class="invalid-feedback">
                                          <strong>{{ $errors->first('email') }}</strong>
                                       </span>
                                    @endif
                                  </div>
                               </div>
                               
                               <div class="input-box">
                                  <label class="label-text">Password</label>
                                  <div class="form-group">
                                     <span class="la la-lock form-icon"></span>
                                     <input class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" value="{{ old('password') }}" required type="password" placeholder="Type password">
                                    @if ($errors->has('password'))
                                       <span class="invalid-feedback">
                                          <strong>{{ $errors->first('password') }}</strong>
                                       </span>
                                    @endif
                                  </div>
                               </div>
                   
                               <div class="input-box">
                                  <label class="label-text">Repeat Password</label>
                                  <div class="form-group">
                                     <span class="la la-lock form-icon"></span>
                                     <input class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}" name="password_confirmation" value="{{ old('password_confirmation') }}" required type="password" placeholder="Type again password">
                                    @if ($errors->has('password_confirmation'))
                                       <span class="invalid-feedback">
                                          <strong>{{ $errors->first('password_confirmation') }}</strong>
                                       </span>
                                    @endif
                                  </div>
                               </div>
                  
                               <div class="mt-4 mb-4">
                                 <button type="submit" class="theme-btn w-100">Register</button>
                               </div>
                               <div class="action-box text-center">
                                  <p class="font-size-14"><a href="Login.html">Already Registered User? Click here to login</a></p>
                                 
                               </div>
                            </form>
                         </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
              
            </div>
        </div>
    </div>
</section>

@endsection