<?php

namespace App\Console\Commands;

use App\Services\Fiscal\SefazDistribuicaoService;
use Illuminate\Console\Command;

class ImportarNfeDoErp extends Command
{
    protected $signature = 'nfe:importar-erp {--de= : Emissão a partir de (AAAA-MM-DD)} {--ate= : Emissão até (AAAA-MM-DD)}';

    protected $description = 'Importa do ERP Singular as NF-e recebidas (resumos, XMLs, ciencia e ultimo NSU) para a tela NF-e Recebidas.';

    public function handle(SefazDistribuicaoService $svc)
    {
        set_time_limit(0);
        $this->info('Importando do ERP...');
        $r = $svc->importarDoErp(function ($lidos) {
            $this->output->write("\r  lidos: $lidos");
        }, $this->option('de') ?: null, $this->option('ate') ?: null);
        $this->newLine();
        $this->info("Resumos: {$r['resumos']} | XML completos: {$r['completos']} | ja existentes/ignorados: {$r['ignorados']} | ciencias marcadas: {$r['ciencias']}");
        $this->info("Ultimo NSU: {$r['nsu']}");
        return 0;
    }
}
