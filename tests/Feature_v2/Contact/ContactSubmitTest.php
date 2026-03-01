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
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequirePro;

class ContactSubmitTest extends BaseApiWithDataTest
{
	use RequirePro;

	public function setUp(): void
	{
		parent::setUp();

		$this->requirePro();
	}

	public function tearDown(): void
	{
		$this->resetPro();

		parent::tearDown();
	}

	public function testPublicSubmitSuccess(): void
	{
		Configs::set('contact_form_security_question', '');
		Configs::set('contact_form_custom_consent_required', '0');

		$response = $this->postJson('Contact', [
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
		$response = $this->postJson('Contact', [
			'email' => 'john@example.com',
			'message' => 'This is a test message that is long enough.',
		]);

		$this->assertUnprocessable($response);
	}

	public function testPublicSubmitRequiresEmail(): void
	{
		$response = $this->postJson('Contact', [
			'name' => 'John Doe',
			'message' => 'This is a test message that is long enough.',
		]);

		$this->assertUnprocessable($response);
	}

	public function testPublicSubmitRequiresMinMessageLength(): void
	{
		$response = $this->postJson('Contact', [
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

		$response = $this->postJson('Contact', [
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

		$response = $this->postJson('Contact', [
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
		Configs::set('contact_form_custom_consent_required', '1');

		$response = $this->postJson('Contact', [
			'name' => 'John Doe',
			'email' => 'john@example.com',
			'message' => 'This is a test message that is long enough.',
			'consent_agreed' => false,
		]);

		$this->assertUnprocessable($response);

		Configs::set('contact_form_custom_consent_required', '0');
	}
}
