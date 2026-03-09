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
use App\Http\Requests\Album\GetAlbumPhotosRequest;
use App\Models\Tag;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Tests\Unit\Http\Requests\Base\BaseRequestTest;

class GetAlbumPhotosRequestTest extends BaseRequestTest
{
	public function testAuthorization(): void
	{
		Gate::shouldReceive('check')
			->with(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, null])
			->andReturn(true);

		$request = new GetAlbumPhotosRequest();

		$this->assertTrue($request->authorize());
	}

	public function testRulesIncludeTagFilters(): void
	{
		$request = new GetAlbumPhotosRequest();

		$rules = $request->rules();

		$this->assertArrayHasKey(RequestAttribute::ALBUM_ID_ATTRIBUTE, $rules);
		$this->assertArrayHasKey(RequestAttribute::PAGE_ATTRIBUTE, $rules);
		$this->assertArrayHasKey('tag_ids', $rules);
		$this->assertArrayHasKey('tag_ids.*', $rules);
		$this->assertArrayHasKey('tag_logic', $rules);
	}

	public function testValidTagIdsArrayIsAccepted(): void
	{
		$tag1 = Tag::factory()->create();
		$tag2 = Tag::factory()->create();

		$request = new GetAlbumPhotosRequest();
		$request->merge([
			RequestAttribute::ALBUM_ID_ATTRIBUTE => 'unsorted', // Use smart album ID
			'tag_ids' => [$tag1->id, $tag2->id],
			'tag_logic' => 'OR',
		]);

		$rules = $request->rules();
		$validator = \Validator::make($request->all(), $rules);

		$this->assertFalse($validator->fails());
	}

	public function testValidTagLogicOrIsAccepted(): void
	{
		$request = new GetAlbumPhotosRequest();
		$request->merge([
			RequestAttribute::ALBUM_ID_ATTRIBUTE => 'unsorted', // Use smart album ID
			'tag_logic' => 'OR',
		]);

		$rules = $request->rules();
		$validator = \Validator::make($request->all(), $rules);

		$this->assertFalse($validator->fails());
	}

	public function testValidTagLogicAndIsAccepted(): void
	{
		$request = new GetAlbumPhotosRequest();
		$request->merge([
			RequestAttribute::ALBUM_ID_ATTRIBUTE => 'unsorted', // Use smart album ID
			'tag_logic' => 'AND',
		]);

		$rules = $request->rules();
		$validator = \Validator::make($request->all(), $rules);

		$this->assertFalse($validator->fails());
	}

	public function testEmptyTagIdsArrayIsTreatedAsNoFilter(): void
	{
		$request = new GetAlbumPhotosRequest();
		$request->merge([
			RequestAttribute::ALBUM_ID_ATTRIBUTE => 'unsorted', // Use smart album ID
			'tag_ids' => [],
		]);

		$rules = $request->rules();
		$validator = \Validator::make($request->all(), $rules);

		$this->assertFalse($validator->fails());
	}

	public function testInvalidTagLogicValueIsRejected(): void
	{
		$request = new GetAlbumPhotosRequest();
		$request->merge([
			RequestAttribute::ALBUM_ID_ATTRIBUTE => 'unsorted', // Use smart album ID
			'tag_logic' => 'INVALID',
		]);

		$rules = $request->rules();
		$validator = \Validator::make($request->all(), $rules);

		$this->assertTrue($validator->fails());
		$this->assertArrayHasKey('tag_logic', $validator->errors()->toArray());
	}

	public function testMissingTagIdsIsAccepted(): void
	{
		$request = new GetAlbumPhotosRequest();
		$request->merge([
			RequestAttribute::ALBUM_ID_ATTRIBUTE => 'unsorted', // Use smart album ID
		]);

		$rules = $request->rules();
		$validator = \Validator::make($request->all(), $rules);

		$this->assertFalse($validator->fails());
	}

	public function testTagIdsAccessorReturnsEmptyArrayWhenNotSet(): void
	{
		$request = new GetAlbumPhotosRequest();

		// tagIds() should return empty array before request is processed
		$this->assertEquals([], $request->tagIds());
	}

	public function testTagLogicAccessorDefaultsToOr(): void
	{
		$request = new GetAlbumPhotosRequest();

		// tagLogic() should default to 'OR' before request is processed
		$this->assertEquals('OR', $request->tagLogic());
	}

	public function testAllInvalidTagIdsThrowsValidationException(): void
	{
		$request = new GetAlbumPhotosRequest();
		$request->merge([
			RequestAttribute::ALBUM_ID_ATTRIBUTE => 'unsorted',
			'tag_ids' => [99999, 99998], // Non-existent tag IDs
		]);

		$rules = $request->rules();
		$validator = \Validator::make($request->all(), $rules);

		// Call withValidator to trigger custom validation
		$request->withValidator($validator);

		try {
			$validator->validate();
			$this->fail('Expected ValidationException was not thrown');
		} catch (ValidationException $e) {
			// Should fail because ALL tag IDs are invalid
			$errors = $e->errors();
			$this->assertArrayHasKey('tag_ids', $errors);
			$this->assertStringContainsString('No valid tags found', $errors['tag_ids'][0]);
		}
	}
}
