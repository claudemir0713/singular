<?php

namespace App\Providers;

use App\Database\Firebird25Connection;
use App\Models\User;
use Closure;
use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;
use PDO;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Servidor Firebird 2.5: troca a gramática do pacote (que gera sintaxe do Firebird 4+)
        Connection::resolverFor('firebird', function (PDO|Closure $pdo, string $database = '', string $tablePrefix = '', array $config = []) {
            return new Firebird25Connection($pdo, $database, $tablePrefix, $config);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('layouts.model', function ($view) {
            $menu = User::montarMenu();
            $view->with([
                "menu"=>$menu
            ]);
        });

    }
}
