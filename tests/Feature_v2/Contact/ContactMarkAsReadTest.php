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

class ContactMarkAsReadTest extends BaseApiWithDataTest
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

	public function testAdminCanMarkAsRead(): void
	{
		$message = ContactMessage::factory()->create(['is_read' => false]);

		$response = $this->actingAs($this->admin)->patchJson('Contact', [
			'id' => $message->id,
			'is_read' => true,
		]);

		$this->assertOk($response);
		$response->assertJson(['is_read' => true]);
	}

	public function testNonAdminCannotMarkAsRead(): void
	{
		$message = ContactMessage::factory()->create(['is_read' => false]);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Contact', [
			'id' => $message->id,
			'is_read' => true,
		]);

		$this->assertForbidden($response);
	}

	public function testNonAuthenticatedCannotMarkAsRead(): void
	{
		$message = ContactMessage::factory()->create(['is_read' => false]);

		$response = $this->actingAsGuest()->patchJson('Contact', [
			'id' => $message->id,
			'is_read' => true,
		]);

		$this->assertUnauthorized($response);
	}
}
