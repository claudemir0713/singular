<!DOCTYPE html>
<html lang="pt">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{env('APP_NAME')}}</title>
        <link rel="shortcut icon" href=" {{ asset('img/icone.png') }} ">
        <link href="{{ asset('css/bootstrap-select.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

        <!--external css-->
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        <link href="{{ asset('css/sweetalert2.min.css') }}" rel="stylesheet">

        <script src="https://kit.fontawesome.com/a076d05399.js"></script>


        {{-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous"> --}}



        <!-- javascripts -->

        <link href="{{ asset('css/bootstrap-select.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

        <!--external css-->
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        <link href="{{ asset('css/sweetalert2.min.css') }}" rel="stylesheet">

        {{-- <script src="https://kit.fontawesome.com/a076d05399.js"></script>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous"> --}}

        <link rel="stylesheet" href="{{ asset('css/v5.7.0_all.css') }}" crossorigin="anonymous">
        <script src="{{ asset('js/9083a84e48.js') }}" crossorigin="anonymous"></script>



        <script src="{{ asset('js/jquery.min.js')}}"></script>
        <script src="{{ asset('js/popper.min.js')}}"></script>
        <script src="{{ asset('js/bootstrap4_5_0.min.js') }}"></script>


        <!-- javascripts -->
        <script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
        <script src="{{ asset('js/scripts.js') }}?v={{ filemtime(public_path('js/scripts.js')) }}"></script>

        <!-- nice scroll -->
        <script src="{{ asset('js/jquery.scrollTo.min.js') }}"></script>
        <script src="{{ asset('js/jquery.nicescroll.js') }}" type="text/javascript"></script>

        <script src="{{ asset('js/sweetalert2.all.min.js') }}" type="text/javascript"></script>

        <!-- Chosen -->
        <script src="{{ asset('js/chosen.jquery.min.js') }}" type="text/javascript"></script>
        <link href="{{ asset('css/chosen.css') }}" rel="stylesheet">

        @php
            $version_js = env('VERSAO_JS');
        @endphp

        <script src="{{ asset('js/functions.js?v='.$version_js) }}" type="text/javascript"></script>
        <script src="{{ asset('js/custom.js?v='.$version_js) }}" type="text/javascript"></script>

    </head>

    <body>
        <script>try{var _m=localStorage.getItem("menu_recolhido2");if(_m===null)_m=window.innerWidth<=768?"1":"0";if(_m==="1")document.body.classList.add("menu-recolhido");}catch(e){}</script>
        <!-- container section start -->
        <section id="container" class="">
            <input type="hidden" name="appurl" id="appurl" value="{{ url('/') }}" >

            <header class="header dark-bg">
                <div class="toggle-nav">
                    <div class="icon-reorder tooltips" data-original-title="Toggle Navigation" data-placement="bottom"><i class="fas fa-bars"></i></div>
                </div>
                <!--logo start-->
                    <a href="#" class="logo">
                        <img src=" {{asset('img/'.env('APP_NAME').'.png')}} " height="40"> &nbsp; &nbsp;<i>{{env('APP_NAME')}}</i>
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


    <script>
jQuery(function($){
    function salvar(r){ try { localStorage.setItem('menu_recolhido2', r ? '1' : '0'); } catch(e) {} }
    // dicas (tooltip) nos ícones quando a barra está reduzida
    $('#sidebar .sidebar-menu > li > a').each(function(){
        var t = $.trim($(this).find('.text').first().text());
        if (t) $(this).attr('title', t);
    });
    var _ult = 0;
    $(document).off('click.menu').on('click.menu', '.toggle-nav', function(e){
        e.stopImmediatePropagation();
        var agora = Date.now(); if (agora - _ult < 300) return; _ult = agora;
        salvar(document.body.classList.toggle('menu-recolhido'));
    });
    // clicar num grupo de menu com a barra reduzida expande a barra para mostrar o submenu
    $(document).on('click', 'body.menu-recolhido #sidebar .sub-menu > a', function(){
        document.body.classList.remove('menu-recolhido');
        salvar(false);
    });
    // neutraliza o ajuste automático do tema que escondia o menu em telas estreitas
    function normalizar(){ $('#container,#main-content').removeClass('sidebar-closed'); $('#sidebar > ul').css('display',''); }
    normalizar(); $(window).on('resize load', function(){ setTimeout(normalizar, 0); });
    // ao voltar (botão voltar / cache de página) reaplica o estado salvo do menu
    function reaplicar(){
        var m = null;
        try { m = localStorage.getItem('menu_recolhido2'); } catch(e) {}
        if (m === null) m = window.innerWidth <= 768 ? '1' : '0';
        document.body.classList.toggle('menu-recolhido', m === '1');
        normalizar();
    }
    window.addEventListener('pageshow', function(){ reaplicar(); setTimeout(reaplicar, 50); });
});
</script>
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

