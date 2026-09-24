@extends('layouts.app')

@section('title', 'Login - NutriMate')

@section('body-class', 'bg-gradient-primary')

@section('content')

<div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

        <div class="col-xl-10 col-lg-12 col-md-9">

            <div class="card o-hidden border-0 shadow-lg my-5">

                <div class="card-body p-0">

                    <!-- Nested Row -->
                    <div class="row">

                        <!-- Logo NutriMate -->
                        <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center">

                            <img
                                src="{{ asset('img/nutrimate.png') }}"
                                alt="Logo NutriMate"
                                class="img-fluid"
                                style="max-width: 70%;"
                            >

                        </div>


                        <!-- Login -->
                        <div class="col-lg-6">

                            <div class="p-5">

                                <div class="text-center">

                                    <h1 class="h4 text-gray-900 mb-4">
                                        Welcome Back!
                                    </h1>

                                </div>


                                {{-- Pesan sukses --}}
                                @if(session('success'))

                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>

                                @endif


                                {{-- Error --}}
                                @if($errors->any())

                                    <div class="alert alert-danger">

                                        @foreach($errors->all() as $error)

                                            <div>
                                                {{ $error }}
                                            </div>

                                        @endforeach

                                    </div>

                                @endif


                                <!-- Form Login -->
                                <form
                                    class="user"
                                    action="{{ route('login.store') }}"
                                    method="POST"
                                >

                                    @csrf


                                    <!-- Email -->
                                    <div class="form-group">

                                        <input
                                            type="email"
                                            class="form-control form-control-user"
                                            id="exampleInputEmail"
                                            name="email"
                                            value="{{ old('email') }}"
                                            placeholder="Enter Email Address..."
                                            required
                                        >

                                    </div>


                                    <!-- Password -->
                                    <div class="form-group">

                                        <input
                                            type="password"
                                            class="form-control form-control-user"
                                            id="exampleInputPassword"
                                            name="password"
                                            placeholder="Password"
                                            required
                                        >

                                    </div>


                                    <!-- Remember Me -->
                                    <div class="form-group">

                                        <div class="custom-control custom-checkbox small">

                                            <input
                                                type="checkbox"
                                                class="custom-control-input"
                                                id="customCheck"
                                                name="remember"
                                            >

                                            <label
                                                class="custom-control-label"
                                                for="customCheck"
                                            >
                                                Remember Me
                                            </label>

                                        </div>

                                    </div>


                                    <!-- Login Button -->
                                    <button
                                        type="submit"
                                        class="btn btn-primary btn-user btn-block"
                                    >
                                        Login
                                    </button>

                                </form>


                                <hr>


                                <!-- Forgot Password -->
                                <div class="text-center">

                                    <a
                                        class="small"
                                        href="#"
                                    >
                                        Forgot Password?
                                    </a>

                                </div>


                                <!-- Register -->
                                <div class="text-center">

                                    <a
                                        class="small"
                                        href="{{ route('register') }}"
                                    >
                                        Create an Account!
                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection