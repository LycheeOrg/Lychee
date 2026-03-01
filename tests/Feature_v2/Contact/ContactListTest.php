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

class ContactListTest extends BaseApiWithDataTest
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

	public function testAdminCanListMessages(): void
	{
		ContactMessage::factory()->count(3)->create();

		$response = $this->actingAs($this->admin)->getJson('Contact');

		$this->assertOk($response);
		$response->assertJsonStructure(['data', 'total', 'per_page', 'current_page']);
	}

	public function testNonAdminCannotListMessages(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Contact');

		$this->assertForbidden($response);
	}

	public function testUnauthenticatedCannotListMessages(): void
	{
		$response = $this->getJson('Contact');

		$this->assertUnauthorized($response);
	}
}
