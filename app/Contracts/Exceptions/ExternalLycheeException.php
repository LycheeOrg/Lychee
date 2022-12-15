<?php

namespace App\Contracts\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

interface ExternalLycheeException extends LycheeException, HttpExceptionInterface
{
}
