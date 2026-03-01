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
use App\Enum\AlbumTitleColor;
use App\Enum\AlbumTitlePosition;
use App\Factories\AlbumFactory;
use App\Http\Requests\Album\UpdateAlbumHeaderRequest;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Tests\Unit\Http\Requests\Base\BaseRequestTest;

class UpdateAlbumHeaderRequestTest extends BaseRequestTest
{
	public function testAuthorization(): void
	{
		$albumMock = $this->createMock(Album::class);
		$baseAlbumImpl = new BaseAlbumImpl();
		$albumMock->method('__get')->willReturnCallback(function (string $key) use ($baseAlbumImpl) {
			if ($key === 'base_class') {
				return $baseAlbumImpl;
			}

			return null;
		});
		Config::set('features.populate-request-macros', true);

		Gate::shouldReceive('check')
			->with(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $albumMock])
			->andReturn(true);

		$mockAlbumFactory = $this->createMock(AlbumFactory::class);
		$mockAlbumFactory->method('findBaseAlbumOrFail')
			->willReturn($albumMock);
		$this->app->instance(AlbumFactory::class, $mockAlbumFactory);
		$request = new UpdateAlbumHeaderRequest();
		$request->setContainer($this->app);
		$request->setRedirector($this->app['redirect']);

		$request->merge([
			RequestAttribute::ALBUM_ID_ATTRIBUTE => 'ePN3Y_kA16KtZGXmxv-kdBrg',
			RequestAttribute::ALBUM_TITLE_COLOR_ATTRIBUTE => AlbumTitleColor::WHITE->value,
			RequestAttribute::ALBUM_TITLE_POSITION_ATTRIBUTE => AlbumTitlePosition::TOP_LEFT->value,
			RequestAttribute::HEADER_PHOTO_FOCUS_ATTRIBUTE => ['x' => 0.5, 'y' => -0.3],
		]);

		$request->validateResolved();

		$this->assertTrue($request->authorize());
	}

	public function testRules(): void
	{
		$request = new UpdateAlbumHeaderRequest();
		Config::set('features.populate-request-macros', true);

		$rules = $request->rules();

		$expectedRuleMap = [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::ALBUM_TITLE_COLOR_ATTRIBUTE => ['present', 'nullable', new Enum(AlbumTitleColor::class)],
			RequestAttribute::ALBUM_TITLE_POSITION_ATTRIBUTE => ['present', 'nullable', new Enum(AlbumTitlePosition::class)],
			RequestAttribute::HEADER_PHOTO_FOCUS_ATTRIBUTE => ['present', 'array'],
			RequestAttribute::HEADER_PHOTO_FOCUS_ATTRIBUTE . '.x' => ['numeric', 'between:-1,1'],
			RequestAttribute::HEADER_PHOTO_FOCUS_ATTRIBUTE . '.y' => ['numeric', 'between:-1,1'],
		];
		$this->assertCount(count($expectedRuleMap), $rules);

		foreach ($expectedRuleMap as $key => $value) {
			$this->assertEquals($value, $rules[$key]);
		}
	}
}
