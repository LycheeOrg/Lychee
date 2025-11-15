<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Tests\ImageProcessing\Import;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class ImportFromServerOptionsTest extends BaseApiWithDataTest
{
	public function testOptionsEndpointAsGuest(): void
	{
		$response = $this->getJson('/Import');
		$this->assertUnauthorized($response);
	}

	public function testOptionsEndpointLoggedIn(): void
	{
		$response = $this->actingAs($this->userLocked)->getJson('/Import');
		$this->assertForbidden($response);
	}

	public function testOptionsEndpointAsOwner(): void
	{
		$response = $this->actingAs($this->admin)->getJson('/Import');
		$this->assertOk($response);
	}
}
