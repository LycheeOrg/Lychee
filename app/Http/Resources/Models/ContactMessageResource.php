<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\ContactMessage;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ContactMessageResource extends Data
{
	public int $id;
	public string $name;
	public string $email;
	public string $message;
	public bool $is_read;
	public string $created_at;

	public function __construct(ContactMessage $contact_message)
	{
		$this->id = $contact_message->id;
		$this->name = $contact_message->name;
		$this->email = $contact_message->email;
		$this->message = $contact_message->message;
		$this->is_read = $contact_message->is_read;
		$this->created_at = $contact_message->created_at->toIso8601String();
	}
}
