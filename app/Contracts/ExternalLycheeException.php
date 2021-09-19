<?php

namespace App\Contracts;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

interface ExternalLycheeException extends LycheeException, HttpExceptionInterface
{
}
