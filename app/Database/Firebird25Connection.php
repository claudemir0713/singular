<?php

namespace App\Database;

use HarryGulliford\Firebird\FirebirdConnection;

class Firebird25Connection extends FirebirdConnection
{
    protected function getDefaultQueryGrammar()
    {
        return new Firebird25Grammar($this);
    }
}
