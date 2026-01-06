<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\ImageProcessing\Import;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class ImportFromServerBrowseTest extends BaseApiWithDataTest
{
	public function testBrowseEndpointAsGuest(): void
	{
		$response = $this->getJsonWithData('/Import::browse', ['directory' => 'tests']);
		$this->assertUnauthorized($response);
	}

	public function testBrowseEndpointLoggedIn(): void
	{
		$response = $this->actingAs($this->userLocked)->getJsonWithData('/Import::browse', ['directory' => 'tests']);
		$this->assertForbidden($response);
	}

	public function testBrowseEndpointAsOwnerWrongDir(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('/Import::browse', ['directory' => 'wrong-dir']);
		$this->assertUnprocessable($response);
	}

	public function testBrowseEndpointAsOwner(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('/Import::browse', ['directory' => 'tests']);
		$this->assertOk($response);
		$content = $response->json();
		// We have to sort in order to have a predictable order for the test.
		// The order returned by the filesystem is not predictable.
		sort($content);
		self::assertEquals(['..', 'Constants', 'Feature_v2',  'ImageProcessing', 'Install', 'Precomputing', 'Samples', 'Traits', 'Unit', 'Webshop', 'docker'], $content);
	}
}
