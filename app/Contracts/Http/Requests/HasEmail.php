<?php

namespace App\Contracts\Http\Requests;

interface HasEmail
{
    /**
     * Get the email address.
     */
    public function email(): string;
}
