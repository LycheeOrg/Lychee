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
use App\Http\Requests\Album\UnlockAlbumRequest;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use App\Rules\PasswordRule;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;
use Tests\Unit\Http\Requests\Base\BaseRequestTest;

class UnlockAlbumRequestTest extends BaseRequestTest
{
	public function testAuthorization()
	{
		Gate::shouldReceive('check')
			->with(AlbumPolicy::CAN_TRANSFER, [AbstractAlbum::class, null])
			->andReturn(true);

		$request = new UnlockAlbumRequest();
		$request->setContainer($this->app);
		$request->setRedirector($this->app['redirect']);
		$request->merge([
			RequestAttribute::ALBUM_ID_ATTRIBUTE => 'ePN3Y_kA16KtZGXmxv-kdBrg',
			RequestAttribute::PASSWORD_ATTRIBUTE => '123',
		]);

		$albumMock = $this->createMock(Album::class);

		$mockAlbumFactory = $this->createMock(AlbumFactory::class);
		$mockAlbumFactory->method('findBaseAlbumOrFail')
			->willReturn($albumMock);
		$this->app->instance(AlbumFactory::class, $mockAlbumFactory);

		$request = new UnlockAlbumRequest();
		$request->setContainer($this->app);
		$request->setRedirector($this->app['redirect']);

		$request->merge([
			RequestAttribute::ALBUM_ID_ATTRIBUTE => 'ePN3Y_kA16KtZGXmxv-kdBrg',
			RequestAttribute::PASSWORD_ATTRIBUTE => 'password123',
		]);

		$request->validateResolved();
		$this->assertTrue($request->authorize());
	}

	public function testRules(): void
	{
		$request = new UnlockAlbumRequest();

		$rules = $request->rules();

		$expectedRuleMap = [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['required', new PasswordRule(false)],
		];
		$this->assertCount(count($expectedRuleMap), $rules); // only validating the first 7 rules & the GRANTS_UPLOAD_ATTRIBUTE is tested afterwards

		foreach ($expectedRuleMap as $key => $value) {
			$this->assertEquals($value, $rules[$key]);
		}
	}
}