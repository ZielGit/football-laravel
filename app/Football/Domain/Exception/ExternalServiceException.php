<?php

declare(strict_types=1);

namespace App\Football\Domain\Exception;

use RuntimeException;

/** Base de todos los fallos al hablar con football-data.org. */
abstract class ExternalServiceException extends RuntimeException
{

}
