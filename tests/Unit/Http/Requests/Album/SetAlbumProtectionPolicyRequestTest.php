<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\Album\SetAlbumProtectionPolicyRequest;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use App\Rules\BooleanRequireSupportRule;
use App\Rules\PasswordRule;
use Illuminate\Support\Facades\Gate;
use Tests\Unit\Http\Requests\Base\BaseRequestTest;

class SetAlbumProtectionPolicyRequestTest extends BaseRequestTest
{
	public function testAuthorization()
	{
		Gate::shouldReceive('check')
			->with(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, null])
			->andReturn(true);

		$request = new SetAlbumProtectionPolicyRequest();
		$this->assertTrue($request->authorize());
	}

	public function testRules(): void
	{
		$request = new SetAlbumProtectionPolicyRequest();

		$rules = $request->rules();

		$expectedRuleMap = [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['sometimes', new PasswordRule(true)],
			RequestAttribute::IS_PUBLIC_ATTRIBUTE => 'required|boolean',
			RequestAttribute::IS_LINK_REQUIRED_ATTRIBUTE => 'required|boolean',
			RequestAttribute::IS_NSFW_ATTRIBUTE => 'required|boolean',
			RequestAttribute::GRANTS_DOWNLOAD_ATTRIBUTE => 'required|boolean',
			RequestAttribute::GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE => 'required|boolean',
		];
		$this->assertCount(count($expectedRuleMap) + 1, $rules); // only validating the first 7 rules & the GRANTS_UPLOAD_ATTRIBUTE is tested afterwards

		foreach ($expectedRuleMap as $key => $value) {
			$this->assertEquals($value, $rules[$key]);
		}
		$grantsUploadAttribute = $rules[RequestAttribute::GRANTS_UPLOAD_ATTRIBUTE];
		$grantsUploadAttribute = array_values($grantsUploadAttribute);
		$grantsUploadAttribute[0] = 'required';
		$grantsUploadAttribute[1] = 'boolean';
		$this->assertInstanceOf(BooleanRequireSupportRule::class, $grantsUploadAttribute[2]);
	}
}