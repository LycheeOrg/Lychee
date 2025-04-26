<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\DownloadVariantType;
use App\Factories\AlbumFactory;
use App\Http\Requests\Album\ZipRequest;
use App\Models\TagAlbum;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDListRule;
use App\Rules\RandomIDListRule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Tests\Unit\Http\Requests\Base\BaseRequestTest;

class ZipRequestTest extends BaseRequestTest
{
	public function testAuthorization()
	{
		$albumMock = $this->createMock(TagAlbum::class);

		$albumMockCollection = collect([$albumMock]);

		Gate::shouldReceive('check')
			->times(2)
			->with(AlbumPolicy::CAN_DOWNLOAD, $albumMock)
			->andReturn(true);

		$mockAlbumFactory = $this->createMock(AlbumFactory::class);
		$mockAlbumFactory->method('findAbstractAlbumsOrFail')
			->willReturn($albumMockCollection);
		$this->app->instance(AlbumFactory::class, $mockAlbumFactory);

		$request = new ZipRequest();
		$request->setContainer($this->app);
		$request->setRedirector($this->app['redirect']);
		$request->merge([
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => 'ePN3Y_kA16KtZGXmxv-kdBrg',
		]);

		$request->validateResolved(); // hydrate the request Class with the data before authorizing . Fighting the framework a bit

		$this->assertTrue($request->authorize());
	}

	public function testRules(): void
	{
		$request = new ZipRequest();

		$rules = $request->rules();

		$expectedRuleMap = [
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => ['sometimes', new AlbumIDListRule()],
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => ['sometimes', new RandomIDListRule()],
			RequestAttribute::SIZE_VARIANT_ATTRIBUTE => ['required_if_accepted:photos_ids', new Enum(DownloadVariantType::class)],
		];
		$this->assertCount(count($expectedRuleMap), $rules); // only validating the first 7 rules & the GRANTS_UPLOAD_ATTRIBUTE is tested afterwards

		foreach ($expectedRuleMap as $key => $value) {
			$this->assertEquals($value, $rules[$key]);
		}
	}
}