<!DOCTYPE html>
<html lang="pt">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{env('APP_NAME')}}</title>
        <link rel="shortcut icon" href=" {{ asset('img/icone.png') }} ">
        {{-- <link href="{{ asset('css/bootstrap-select.min.css') }}" rel="stylesheet"> --}}
        <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

        <!--external css-->
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        <link href="{{ asset('css/sweetalert2.min.css') }}" rel="stylesheet">


        <link href="{{ asset('fontawesome/css/all.css') }}" rel="stylesheet" >

        <!-- javascripts -->
        <script src="{{ asset('js/jquery.min.js')}}"></script>
        <script src="{{ asset('js/popper.min.js')}}"></script>
        <script src="{{ asset('js/bootstrap4_5_0.min.js') }}"></script>

        <script src="{{ asset('js/bootstrap-select.min.js') }}"></script>

        <script src="{{ asset('js/jquery-1.8.3.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/jquery.nicescroll.js') }}" type="text/javascript"></script>
        <script src="{{ asset('js/scripts.js') }}"></script>
        <!-- nice scroll -->
        <script src="{{ asset('js/jquery.scrollTo.min.js') }}"></script>
        <script src="{{ asset('js/jquery.nicescroll.js') }}" type="text/javascript"></script>

        <script src="{{ asset('js/sweetalert2.all.min.js') }}" type="text/javascript"></script>

        <!-- Chosen -->
        <script src="{{ asset('js/chosen.jquery.min.js') }}" type="text/javascript"></script>
        <link href="{{ asset('css/chosen.css') }}" rel="stylesheet">

        <script src="{{ asset('js/functions.js') }}" type="text/javascript"></script>
        <script src="{{ asset('js/custom.js') }}" type="text/javascript"></script>

    </head>

    <body>
        <!-- container section start -->
        <section id="container" class="">
            <input type="hidden" name="appurl" id="appurl" value="{{ url('/') }}" >

            <header class="header dark-bg">
                <div class="toggle-nav">
                    <div class="icon-reorder tooltips" data-original-title="Toggle Navigation" data-placement="bottom"><i class="icon_menu"></i></div>
                </div>
                <!--logo start-->
                    <a href="#" class="logo">
                        <img src=" {{asset("img/{{env('APP_NAME')}}.png")}} " height="40"> &nbsp; &nbsp;<i>{{env('APP_NAME')}}</i>
                    </a>
                <!--logo end-->
                <div class="">

                </div>
                <div class="top-nav notification-row">
                    <input type="hidden" id='userEmail' name='userEmail' value="{{Auth::user()->email}}">
                    <input type="hidden" id='userPassword' name='userPassword' value="{{Auth::user()->password}}">
                <!-- notificatoin dropdown start-->
                    <ul class="nav pull-right top-menu">
                        <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="profile-ava">
                                <i class="fas fa-user"></i>
                            </span>
                            <span class="username"> {{ Auth::user()->name }} </span>
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu extended logout">
                            <div class="log-arrow-up"></div>
                            <li>
                                <button type="button" class="btn btn-light dropdown-item" data-toggle="modal" data-target="#ModalSenha" data-backdrop="static"  data-keyboard="false">
                                    <i class="fas fa-user"></i> Trocar Senha
                                </button>
                            </li>

                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fa fa-sign-out"> Sair</i>
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                        </li>
                        <!-- user login dropdown end -->
                    </ul>
                <!-- notificatoin dropdown end-->
                </div>
            </header>

            <!--header end-->

            <!--sidebar start-->
                {!! $menu !!}
            <!--sidebar end-->
        </section>
        <section id="main-content">
            <section class="wrapper">
                <div class="col-lg-12">
                    @yield('content')
            </section>
        </section>


    </body>

</html>
@include('layouts.modalSenha')

<script>
    function number_format(number, decimals, dec_point, thousands_sep) {
        number = number.toFixed(decimals);

        var nstr = number.toString()
        nstr += '';
        x = nstr.split('.')
        x1 = x[0]
        x2 = x.length > 1 ? dec_point + x[1] : ''
        var rgx = /(\d+)(\d{3})/

        while (rgx.test(x1))
            x1 = x1.replace(rgx, '$1' + thousands_sep + '$2')

        return x1 + x2
    }
</script>

