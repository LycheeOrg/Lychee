<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\Album\SetPinnedRequest;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;
use Tests\Unit\Http\Requests\Base\BaseRequestTest;

class SetPinnedRequestTest extends BaseRequestTest
{
	public function testAuthorization()
	{
		Gate::shouldReceive('check')
			->with(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, null])
			->andReturn(true);

		$request = new SetPinnedRequest();
		$this->assertTrue($request->authorize());
	}

	public function testRules(): void
	{
		$request = new SetPinnedRequest();

		$rules = $request->rules();

		$expectedRuleMap = [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(true)],
			RequestAttribute::IS_PINNED_ATTRIBUTE => ['required', 'boolean'],
		];

		$this->assertCount(count($expectedRuleMap), $rules);

		foreach ($expectedRuleMap as $key => $value) {
			$this->assertEquals($value, $rules[$key]);
		}
	}
}