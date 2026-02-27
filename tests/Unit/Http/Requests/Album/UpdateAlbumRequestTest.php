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
use App\DTO\PhotoSortingCriterion;
use App\Enum\AspectRatioType;
use App\Enum\ColumnSortingAlbumType;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\LicenseType;
use App\Enum\OrderSortingType;
use App\Enum\PhotoLayoutType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use App\Factories\AlbumFactory;
use App\Http\Requests\Album\UpdateAlbumRequest;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
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

class UpdateAlbumRequestTest extends BaseRequestTest
{
	protected function setUp(): void
	{
		parent::setUp();

		$this->mock_verify = $this->requireSe(); // We need to be a supporter to test the rules
	}

	public function testAuthorization()
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
		$request = new UpdateAlbumRequest();
		$request->setContainer($this->app);
		$request->setRedirector($this->app['redirect']);

		$request->merge([
			RequestAttribute::IS_COMPACT_ATTRIBUTE => true,
			RequestAttribute::IS_PINNED_ATTRIBUTE => false,
			RequestAttribute::ALBUM_ID_ATTRIBUTE => 'ePN3Y_kA16KtZGXmxv-kdBrg',
			RequestAttribute::HEADER_ID_ATTRIBUTE => 'ePN3Y_kA16KtZGXmxv-kdBrg',
			RequestAttribute::TITLE_ATTRIBUTE => 'Test Title',
			RequestAttribute::LICENSE_ATTRIBUTE => LicenseType::CC_BY_1_0->value,
			RequestAttribute::DESCRIPTION_ATTRIBUTE => 'example description',
			RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE => ColumnSortingPhotoType::TAKEN_AT->value,
			RequestAttribute::PHOTO_SORTING_ORDER_ATTRIBUTE => OrderSortingType::ASC->value,
			RequestAttribute::ALBUM_SORTING_COLUMN_ATTRIBUTE => ColumnSortingPhotoType::TITLE->value,
			RequestAttribute::ALBUM_SORTING_ORDER_ATTRIBUTE => OrderSortingType::ASC->value,
			RequestAttribute::ALBUM_ASPECT_RATIO_ATTRIBUTE => AspectRatioType::aspect1byx9->value,
			RequestAttribute::ALBUM_PHOTO_LAYOUT => PhotoLayoutType::JUSTIFIED->value,
			RequestAttribute::COPYRIGHT_ATTRIBUTE => 'Copyright (c) 2017-2018 Tobias Reich',
			RequestAttribute::ALBUM_TIMELINE_ALBUM => TimelineAlbumGranularity::DEFAULT->value,
			RequestAttribute::ALBUM_TIMELINE_PHOTO => TimelineAlbumGranularity::DAY->value,
			//			RequestAttribute::ALBUM_PHOTO_LAYOUT => PhotoSortingCriterion::ALBUM_PHOTO_LAYOUT_GRID->value,
		]);

		$request->validateResolved(); // hydrate the request Class with the data before authorizing . Fighting the framework a bit

		$this->assertTrue($request->authorize());
	}

	public function testRules(): void
	{
		$request = new UpdateAlbumRequest();
		Config::set('features.populate-request-macros', true);

		$rules = $request->rules();

		$expectedRuleMap = [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
			RequestAttribute::LICENSE_ATTRIBUTE => ['required', new Enum(LicenseType::class)],
			RequestAttribute::DESCRIPTION_ATTRIBUTE => ['present', new DescriptionRule()],
			RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE => ['present', 'nullable', new Enum(ColumnSortingPhotoType::class)],
			RequestAttribute::PHOTO_SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE,
				'nullable', new Enum(OrderSortingType::class),
			],
			RequestAttribute::ALBUM_SORTING_COLUMN_ATTRIBUTE => ['present', 'nullable', new Enum(ColumnSortingAlbumType::class)],
			RequestAttribute::ALBUM_SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . RequestAttribute::ALBUM_SORTING_COLUMN_ATTRIBUTE,
				'nullable', new Enum(OrderSortingType::class),
			],
			RequestAttribute::ALBUM_ASPECT_RATIO_ATTRIBUTE => ['present', 'nullable', new Enum(AspectRatioType::class)],
			RequestAttribute::ALBUM_PHOTO_LAYOUT => ['present', 'nullable', new Enum(PhotoLayoutType::class)],
			RequestAttribute::COPYRIGHT_ATTRIBUTE => ['present', 'nullable', new CopyrightRule()],
			RequestAttribute::IS_COMPACT_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::IS_PINNED_ATTRIBUTE => ['present', 'boolean'],
			RequestAttribute::HEADER_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			RequestAttribute::ALBUM_TIMELINE_ALBUM => ['present', 'nullable', new Enum(TimelineAlbumGranularity::class), new EnumRequireSupportRule(TimelinePhotoGranularity::class, [TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED], $this->mock_verify)],
			RequestAttribute::ALBUM_TIMELINE_PHOTO => ['present', 'nullable', new Enum(TimelinePhotoGranularity::class), new EnumRequireSupportRule(TimelinePhotoGranularity::class, [TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED], $this->mock_verify)],
			RequestAttribute::SLUG_ATTRIBUTE => ['sometimes', 'nullable', new StringRequireSupportRule(null, $this->mock_verify), new SlugRule($request->input(RequestAttribute::ALBUM_ID_ATTRIBUTE))],
		];
		$this->assertCount(count($expectedRuleMap), $rules); // only validating the first 7 rules & the GRANTS_UPLOAD_ATTRIBUTE is tested afterwards

		foreach ($expectedRuleMap as $key => $value) {
			$this->assertEquals($value, $rules[$key]);
		}
	}
}