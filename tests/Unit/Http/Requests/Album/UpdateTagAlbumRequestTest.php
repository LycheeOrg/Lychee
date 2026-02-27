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
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Enum\PhotoLayoutType;
use App\Enum\TimelinePhotoGranularity;
use App\Factories\AlbumFactory;
use App\Http\Requests\Album\UpdateTagAlbumRequest;
use App\Models\BaseAlbumImpl;
use App\Models\TagAlbum;
use App\Policies\AlbumPolicy;
use App\Rules\CopyrightRule;
use App\Rules\DescriptionRule;
use App\Rules\EnumRequireSupportRule;
use App\Rules\RandomIDRule;
use App\Rules\SlugRule;
use App\Rules\StringRequireSupportRule;
use App\Rules\TitleRule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Tests\Unit\Http\Requests\Base\BaseRequestTest;

class UpdateTagAlbumRequestTest extends BaseRequestTest
{
	protected function setUp(): void
	{
		parent::setUp();

		$this->mock_verify = $this->requireSe();
	}

	public function testAuthorization()
	{
		$tagalbumMock = $this->createMock(TagAlbum::class);
		$baseAlbumImpl = new BaseAlbumImpl();
		$tagalbumMock->method('__get')->willReturnCallback(function (string $key) use ($baseAlbumImpl) {
			if ($key === 'base_class') {
				return $baseAlbumImpl;
			}

			return null;
		});
		Config::set('features.populate-request-macros', true);

		Gate::shouldReceive('check')
			->with(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $tagalbumMock])
			->andReturn(true);

		$mockAlbumFactory = $this->createMock(AlbumFactory::class);
		$mockAlbumFactory->method('findBaseAlbumOrFail')
			->willReturn($tagalbumMock);
		$this->app->instance(AlbumFactory::class, $mockAlbumFactory);

		$request = new UpdateTagAlbumRequest();
		$request->setContainer($this->app);
		$request->setRedirector($this->app['redirect']);

		$request->merge([
			RequestAttribute::ALBUM_ID_ATTRIBUTE => 'ePN3Y_kA16KtZGXmxv-kdBrg',
			RequestAttribute::TITLE_ATTRIBUTE => 'Test Title',
			RequestAttribute::DESCRIPTION_ATTRIBUTE => 'example description',
			RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE => ColumnSortingPhotoType::TAKEN_AT->value,
			RequestAttribute::PHOTO_SORTING_ORDER_ATTRIBUTE => OrderSortingType::ASC->value,
			RequestAttribute::ALBUM_PHOTO_LAYOUT => PhotoLayoutType::JUSTIFIED->value,
			RequestAttribute::ALBUM_TIMELINE_PHOTO => TimelinePhotoGranularity::DEFAULT->value,
			RequestAttribute::COPYRIGHT_ATTRIBUTE => 'Copyright (c) 2017-2018 Tobias Reich',
			RequestAttribute::TAGS_ATTRIBUTE => ['tag1', 'tag2'],
			RequestAttribute::IS_PINNED_ATTRIBUTE => false,
			RequestAttribute::IS_AND_ATTRIBUTE => true,
		]);

		$request->validateResolved(); // hydrate the request Class with the data before authorizing . Fighting the framework a bit

		$this->assertTrue($request->authorize());
	}

	public function testRules(): void
	{
		$request = new UpdateTagAlbumRequest();
		Config::set('features.populate-request-macros', true);

		$rules = $request->rules();

		$expectedRuleMap = [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
			RequestAttribute::DESCRIPTION_ATTRIBUTE => ['present', new DescriptionRule()],
			RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE => ['present', 'nullable', new Enum(ColumnSortingPhotoType::class)],
			RequestAttribute::PHOTO_SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE,
				'nullable', new Enum(OrderSortingType::class),
			],
			RequestAttribute::TAGS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
			RequestAttribute::COPYRIGHT_ATTRIBUTE => ['present', 'nullable', new CopyrightRule()],
			RequestAttribute::ALBUM_PHOTO_LAYOUT => ['present', 'nullable', new Enum(PhotoLayoutType::class)],
			RequestAttribute::ALBUM_TIMELINE_PHOTO => ['present', 'nullable', new Enum(TimelinePhotoGranularity::class), new EnumRequireSupportRule(TimelinePhotoGranularity::class, [TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED], $this->mock_verify)],
			RequestAttribute::IS_PINNED_ATTRIBUTE => ['present', 'boolean'],
			RequestAttribute::IS_AND_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::SLUG_ATTRIBUTE => ['sometimes', 'nullable', new StringRequireSupportRule(null, $this->mock_verify), new SlugRule($request->input(RequestAttribute::ALBUM_ID_ATTRIBUTE))],
		];
		$this->assertCount(count($expectedRuleMap), $rules); // only validating the first 7 rules & the GRANTS_UPLOAD_ATTRIBUTE is tested afterwards

		foreach ($expectedRuleMap as $key => $value) {
			$this->assertEquals($value, $rules[$key]);
		}
	}
}