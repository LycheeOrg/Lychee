<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Contact;

use App\Http\Requests\BaseApiRequest;
use App\Models\ContactMessage;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

class DeleteContactMessageRequest extends BaseApiRequest
{
	protected ContactMessage $contact_message;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, [User::class]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			'id' => ['required', 'integer'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var int $id */
		$id = $values['id'];
		$this->contact_message = ContactMessage::query()->findOrFail($id);
	}

	public function contactMessage(): ContactMessage
	{
		return $this->contact_message;
	}
}
