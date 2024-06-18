<?php

declare(strict_types=1);

namespace App\Contracts\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

interface ExternalLycheeException extends LycheeException, HttpExceptionInterface
{
}
