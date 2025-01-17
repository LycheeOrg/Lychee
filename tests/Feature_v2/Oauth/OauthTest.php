<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\Oauth;

use Illuminate\Support\Facades\Config;
use Tests\Feature_v2\Base\BaseApiV2Test;

class OauthTest extends BaseApiV2Test
{
	public function testGetAnonymous(): void
	{
		Config::set('services.github.client_id', 'something');
		Config::set('services.github.client_secret', 'something');
		Config::set('services.github.redirect', 'something');

		$response = $this->getJson('Oauth');
		$this->assertOk($response);
		$response->assertJson(['github']);

		$response = $this->deleteJson('Oauth', ['provider' => 'github']);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload1)->getJson('Oauth');
		$this->assertOk($response);
		$response->assertJson([[
			'providerType' => 'github',
			'isEnabled' => false,
		]]);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Oauth', ['provider' => 'github']);
		$this->assertNoContent($response);
	}
}
