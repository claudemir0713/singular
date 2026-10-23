<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProdutosSemFichaMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $resultado;

    public function __construct(array $resultado)
    {
        $this->resultado = $resultado;
    }

    public function build()
    {
        return $this->subject('Produtos sem ficha técnica - ' . ($this->resultado['ano_mes'] ?? ''))
            ->view('emails.produtos-sem-ficha');
    }
}
