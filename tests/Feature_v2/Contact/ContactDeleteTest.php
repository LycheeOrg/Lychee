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

use App\Models\ContactMessage;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequirePro;

class ContactDeleteTest extends BaseApiWithDataTest
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

	public function testAdminCanDeleteMessage(): void
	{
		$message = ContactMessage::factory()->create();

		$response = $this->actingAs($this->admin)->deleteJson('Contact', [
			'id' => $message->id,
		]);

		$this->assertNoContent($response);
		$this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
	}

	public function testNonAdminCannotDeleteMessage(): void
	{
		$message = ContactMessage::factory()->create();

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Contact', [
			'id' => $message->id,
		]);

		$this->assertForbidden($response);
	}

	public function testNonAuthenticatedCannotDeleteMessage(): void
	{
		$message = ContactMessage::factory()->create();

		$response = $this->actingAsGuest()->deleteJson('Contact', [
			'id' => $message->id,
		]);

		$this->assertUnauthorized($response);
	}
}
