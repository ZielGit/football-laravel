<?php

namespace App\Exceptions;

use RuntimeException;

class AccountDisabledException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Tu cuenta está desactivada. Contacta a un administrador.');
    }
}
