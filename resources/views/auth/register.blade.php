@extends('layouts.app')

@section('title', 'Register - NutriMate')

@section('body-class', 'bg-gradient-primary')

@section('content')

<div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

        <div class="col-xl-6 col-lg-7 col-md-9">

            <div class="card o-hidden border-0 shadow-lg my-5">

                <div class="card-body p-0">

                    <!-- Form Register -->
                    <div class="row">

                        <div class="col-lg-12">

                            <div class="p-5">

                                <!-- ============================= -->
                                <!-- JUDUL -->
                                <!-- ============================= -->
                                <div class="text-center">

                                    <h1 class="h4 text-gray-900 mb-4">
                                        Create an Account!
                                    </h1>

                                    <p class="text-muted mb-4">
                                        Create your NutriMate account
                                    </p>

                                </div>


                                <!-- ============================= -->
                                <!-- PESAN ERROR -->
                                <!-- ============================= -->
                                @if($errors->any())

                                    <div class="alert alert-danger">

                                        @foreach($errors->all() as $error)

                                            <div>
                                                {{ $error }}
                                            </div>

                                        @endforeach

                                    </div>

                                @endif


                                <!-- ============================= -->
                                <!-- FORM REGISTER -->
                                <!-- ============================= -->
                                <form
                                    class="user"
                                    action="{{ route('register.store') }}"
                                    method="POST"
                                >

                                    @csrf


                                    <!-- ============================= -->
                                    <!-- NAMA -->
                                    <!-- ============================= -->
                                    <div class="form-group">

                                        <input
                                            type="text"
                                            class="form-control form-control-user"
                                            id="name"
                                            name="name"
                                            value="{{ old('name') }}"
                                            placeholder="Full Name"
                                            autocomplete="name"
                                            required
                                        >

                                    </div>


                                    <!-- ============================= -->
                                    <!-- EMAIL -->
                                    <!-- ============================= -->
                                    <div class="form-group">

                                        <input
                                            type="email"
                                            class="form-control form-control-user"
                                            id="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            placeholder="Email Address"
                                            autocomplete="email"
                                            required
                                        >

                                    </div>


                                    <!-- ============================= -->
                                    <!-- PASSWORD -->
                                    <!-- ============================= -->
                                    <div class="form-group row">

                                        <div class="col-sm-6 mb-3 mb-sm-0">

                                            <input
                                                type="password"
                                                class="form-control form-control-user"
                                                id="password"
                                                name="password"
                                                placeholder="Password"
                                                autocomplete="new-password"
                                                required
                                            >

                                        </div>


                                        <!-- KONFIRMASI PASSWORD -->
                                        <div class="col-sm-6">

                                            <input
                                                type="password"
                                                class="form-control form-control-user"
                                                id="password_confirmation"
                                                name="password_confirmation"
                                                placeholder="Repeat Password"
                                                autocomplete="new-password"
                                                required
                                            >

                                        </div>

                                    </div>


                                    <!-- ============================= -->
                                    <!-- TOMBOL REGISTER -->
                                    <!-- ============================= -->
                                    <button
                                        type="submit"
                                        class="btn btn-primary btn-user btn-block"
                                    >
                                        Register Account
                                    </button>

                                </form>


                                <hr>


                                <!-- ============================= -->
                                <!-- LINK LOGIN -->
                                <!-- ============================= -->
                                <div class="text-center">

                                    <a
                                        class="small"
                                        href="{{ route('login') }}"
                                    >
                                        Already have an account? Login!
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