<?php

namespace App\Console;

use App\Jobs\atualizaProdutoMysql;
use App\Jobs\importaObCli;
use App\Jobs\importaSaldoIEstoque;
use App\Models\sigular_estoque_bloco_k;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->job(new importaObCli)->everyMinute()->between('01:00', '23:59');
        // $schedule->job(new importaSaldoIEstoque)->everyMinute()->between('01:00', '23:59');
        $schedule->job(new atualizaProdutoMysql)->everyMinute()->between('06:00', '23:59');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
