<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\Contact;

use App\Models\Configs;
use App\Models\ContactMessage;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class ContactTest extends BaseApiWithDataTest
{
	public function testPublicSubmitSuccess(): void
	{
		Configs::set('contact_form_security_question', '');
		Configs::set('contact_form_custom_consent_text', '');

		$response = $this->postJson('contact', [
			'name' => 'John Doe',
			'email' => 'john@example.com',
			'message' => 'This is a test message that is long enough.',
		]);

		$this->assertOk($response);
		$response->assertJson(['success' => true]);

		$this->assertDatabaseHas('contact_messages', ['email' => 'john@example.com']);
	}

	public function testPublicSubmitRequiresName(): void
	{
		$response = $this->postJson('contact', [
			'email' => 'john@example.com',
			'message' => 'This is a test message that is long enough.',
		]);

		$this->assertUnprocessable($response);
	}

	public function testPublicSubmitRequiresEmail(): void
	{
		$response = $this->postJson('contact', [
			'name' => 'John Doe',
			'message' => 'This is a test message that is long enough.',
		]);

		$this->assertUnprocessable($response);
	}

	public function testPublicSubmitRequiresMinMessageLength(): void
	{
		$response = $this->postJson('contact', [
			'name' => 'John Doe',
			'email' => 'john@example.com',
			'message' => 'Too short',
		]);

		$this->assertUnprocessable($response);
	}

	public function testPublicSubmitSecurityAnswerCorrect(): void
	{
		Configs::set('contact_form_security_question', 'What colour is the sky?');
		Configs::set('contact_form_security_answer', 'Blue');

		$response = $this->postJson('contact', [
			'name' => 'John Doe',
			'email' => 'john@example.com',
			'message' => 'This is a test message that is long enough.',
			'security_answer' => 'blue',
		]);

		$this->assertOk($response);

		Configs::set('contact_form_security_question', '');
		Configs::set('contact_form_security_answer', '');
	}

	public function testPublicSubmitSecurityAnswerWrong(): void
	{
		Configs::set('contact_form_security_question', 'What colour is the sky?');
		Configs::set('contact_form_security_answer', 'Blue');

		$response = $this->postJson('contact', [
			'name' => 'John Doe',
			'email' => 'john@example.com',
			'message' => 'This is a test message that is long enough.',
			'security_answer' => 'Wrong answer',
		]);

		$this->assertUnprocessable($response);

		Configs::set('contact_form_security_question', '');
		Configs::set('contact_form_security_answer', '');
	}

	public function testPublicSubmitConsentRequired(): void
	{
		Configs::set('contact_form_custom_consent_text', 'I agree to the privacy policy');

		$response = $this->postJson('contact', [
			'name' => 'John Doe',
			'email' => 'john@example.com',
			'message' => 'This is a test message that is long enough.',
			'consent_agreed' => false,
		]);

		$this->assertUnprocessable($response);

		Configs::set('contact_form_custom_consent_text', '');
	}

	public function testAdminCanListMessages(): void
	{
		ContactMessage::factory()->count(3)->create();

		$response = $this->actingAs($this->admin)->getJson('contact');

		$this->assertOk($response);
		$response->assertJsonStructure(['data', 'pagination']);
	}

	public function testNonAdminCannotListMessages(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('contact');

		$this->assertForbidden($response);
	}

	public function testUnauthenticatedCannotListMessages(): void
	{
		$response = $this->getJson('contact');

		$this->assertForbidden($response);
	}

	public function testAdminCanMarkAsRead(): void
	{
		$message = ContactMessage::factory()->create(['is_read' => false]);

		$response = $this->actingAs($this->admin)->patchJson('contact', [
			'id' => $message->id,
			'is_read' => true,
		]);

		$this->assertOk($response);
		$response->assertJson(['is_read' => true]);
	}

	public function testNonAdminCannotMarkAsRead(): void
	{
		$message = ContactMessage::factory()->create(['is_read' => false]);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('contact', [
			'id' => $message->id,
			'is_read' => true,
		]);

		$this->assertForbidden($response);
	}

	public function testAdminCanDeleteMessage(): void
	{
		$message = ContactMessage::factory()->create();

		$response = $this->actingAs($this->admin)->deleteJson('contact', [
			'id' => $message->id,
		]);

		$this->assertNoContent($response);
		$this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
	}

	public function testNonAdminCannotDeleteMessage(): void
	{
		$message = ContactMessage::factory()->create();

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('contact', [
			'id' => $message->id,
		]);

		$this->assertForbidden($response);
	}
}
