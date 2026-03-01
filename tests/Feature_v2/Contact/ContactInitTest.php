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

class ContactInitTest extends BaseApiWithDataTest
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

	public function testAdminCanInit(): void
	{
		ContactMessage::factory()->count(3)->create();

		$response = $this->actingAs($this->admin)->getJson('Contact::Init');

		$this->assertOk($response);
	}

	public function testNonAdminCanInit(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Contact::Init');

		$this->assertOk($response);
	}

	public function testUnauthenticatedCanInit(): void
	{
		$response = $this->getJson('Contact::Init');

		$this->assertOk($response);
	}
}
