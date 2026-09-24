<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    {{-- Sidebar Toggle --}}
    <button id="sidebarToggleTop"
            class="btn btn-link d-md-none rounded-circle mr-3">

        <i class="fa fa-bars"></i>

    </button>


    {{-- Page Title --}}
    <div class="d-none d-sm-inline-block">

        <span class="font-weight-bold text-success">
            Dashboard
        </span>

    </div>


    {{-- Right Side --}}
    <ul class="navbar-nav ml-auto">

        <div class="topbar-divider d-none d-sm-block"></div>


        {{-- User --}}
        <li class="nav-item dropdown no-arrow">

            <a class="nav-link dropdown-toggle"
               href="#"
               id="userDropdown"
               role="button"
               data-toggle="dropdown"
               aria-haspopup="true"
               aria-expanded="false">

                <span class="mr-2 d-none d-lg-inline text-gray-600 small">

                    {{ Auth::user()->name }}

                </span>

                <i class="fas fa-user-circle fa-2x text-success"></i>

            </a>


            {{-- Dropdown --}}
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                 aria-labelledby="userDropdown">


                {{-- Profil --}}
                <a class="dropdown-item"
                   href="{{ route('profil.create') }}">

                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>

                    Profil

                </a>


                <div class="dropdown-divider"></div>


                {{-- Logout --}}
                <form action="{{ route('logout') }}"
                      method="POST">

                    @csrf

                    <button type="submit"
                            class="dropdown-item">

                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>

                        Logout

                    </button>

                </form>

            </div>

        </li>

    </ul>

</nav>