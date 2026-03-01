<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Contact;

use App\Http\Requests\Contact\ContactMessagesListRequest;
use App\Http\Requests\Contact\DeleteContactMessageRequest;
use App\Http\Requests\Contact\StoreContactMessageRequest;
use App\Http\Requests\Contact\UpdateContactMessageRequest;
use App\Http\Resources\Collections\ContactMessageCollectionResource;
use App\Http\Resources\GalleryConfigs\ContactConfig;
use App\Http\Resources\Models\ContactMessageResource;
use App\Models\ContactMessage;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ContactController extends Controller
{
	/**
	 * Return the contact form configuration values.
	 *
	 * @return ContactConfig
	 */
	public function init(): ContactConfig
	{
		return new ContactConfig();
	}

	/**
	 * Store a new contact message from a public visitor.
	 *
	 * @param StoreContactMessageRequest $request
	 *
	 * @return array{success:bool,message:string}
	 */
	public function store(StoreContactMessageRequest $request): array
	{
		// Validate security answer if configured
		$security_question = $request->configs()->getValueAsString('contact_form_security_question');
		$security_answer = $request->configs()->getValueAsString('contact_form_security_answer');
		if ($security_question !== '' && $security_answer !== '') {
			if (strcasecmp(trim($request->securityAnswer()), trim($security_answer)) !== 0) {
				abort(422, 'Incorrect answer to the security question.');
			}
		}

		// Validate consent if configured
		$is_consent_required = $request->configs()->getValueAsBool('contact_form_custom_consent_required');
		if ($is_consent_required && !$request->consentAgreed()) {
			abort(422, 'You must agree to the privacy policy.');
		}

		ContactMessage::create([
			'name' => $request->senderName(),
			'email' => $request->senderEmail(),
			'message' => $request->senderMessage(),
			'ip_address' => $request->ip(),
			'user_agent' => $request->userAgent(),
		]);

		return ['success' => true, 'message' => 'Thank you for your message. We will get back to you soon.'];
	}

	/**
	 * List all contact messages (admin only).
	 *
	 * @param ContactMessagesListRequest $request
	 *
	 * @return ContactMessageCollectionResource
	 */
	public function index(ContactMessagesListRequest $request): ContactMessageCollectionResource
	{
		$per_page = min((int) $request->query('per_page', 20), 100);
		$page = max((int) $request->query('page', 1), 1);
		$search = $request->query('search', '');
		$is_read_filter = $request->query('is_read', null);

		$query = ContactMessage::query()->orderBy('created_at', 'desc');

		if (is_string($search) && $search !== '') {
			$escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
			$query->where(function ($q) use ($escaped): void {
				$q->where('name', 'like', '%' . $escaped . '%')
					->orWhere('email', 'like', '%' . $escaped . '%')
					->orWhere('message', 'like', '%' . $escaped . '%');
			});
		}

		if ($is_read_filter !== null) {
			$query->where('is_read', filter_var($is_read_filter, FILTER_VALIDATE_BOOLEAN));
		}

		$total = $query->count();
		$messages = $query->offset(($page - 1) * $per_page)->limit($per_page)->get();

		return new ContactMessageCollectionResource(
			$messages->map(fn (ContactMessage $m) => new ContactMessageResource($m)),
			$total,
			$per_page,
			$page,
		);
	}

	/**
	 * Update a contact message (mark as read/unread).
	 *
	 * @param UpdateContactMessageRequest $request
	 *
	 * @return ContactMessageResource
	 */
	public function update(UpdateContactMessageRequest $request): ContactMessageResource
	{
		$message = $request->contactMessage();
		$message->is_read = $request->isRead();
		$message->save();

		return new ContactMessageResource($message);
	}

	/**
	 * Delete a contact message.
	 *
	 * @param DeleteContactMessageRequest $request
	 *
	 * @return Response
	 */
	public function destroy(DeleteContactMessageRequest $request): Response
	{
		$request->contactMessage()->delete();

		return response()->noContent();
	}
}
