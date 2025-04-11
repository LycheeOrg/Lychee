<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Factories\AlbumFactory;
use App\Http\Requests\Album\SetAsHeaderRequest;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;
use Tests\Unit\Http\Requests\Base\BaseRequestTest;

class SetAsHeaderRequestTest extends BaseRequestTest
{
	public function testAuthorization()
	{
		$albumMock = $this->createMock(Album::class);

		Gate::shouldReceive('check')
			->with(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $albumMock])
			->andReturn(true);

		$mockAlbumFactory = $this->createMock(AlbumFactory::class);
		$mockAlbumFactory->method('findBaseAlbumOrFail')
			->willReturn($albumMock);
		$this->app->instance(AlbumFactory::class, $mockAlbumFactory);

		$request = new SetAsHeaderRequest();
		$request->setContainer($this->app);
		$request->setRedirector($this->app['redirect']);

		$request->merge([
			RequestAttribute::IS_COMPACT_ATTRIBUTE => true,
			RequestAttribute::ALBUM_ID_ATTRIBUTE => 'ePN3Y_kA16KtZGXmxv-kdBrg',
			RequestAttribute::HEADER_ID_ATTRIBUTE => 'ePN3Y_kA16KtZGXmxv-kdBrg',
		]);

		$request->validateResolved(); // hydrate the request Class with the data before authorizing . Fighting the framework a bit

		$this->assertTrue($request->authorize());
	}

	public function testRules(): void
	{
		$request = new SetAsHeaderRequest();

		$rules = $request->rules();

		$expectedRuleMap = [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::HEADER_ID_ATTRIBUTE => ['required', new RandomIDRule(true)],
			RequestAttribute::IS_COMPACT_ATTRIBUTE => ['required', 'boolean'],
		];
		$this->assertCount(count($expectedRuleMap), $rules); // only validating the first 7 rules & the GRANTS_UPLOAD_ATTRIBUTE is tested afterwards

		foreach ($expectedRuleMap as $key => $value) {
			$this->assertEquals($value, $rules[$key]);
		}
	}
}