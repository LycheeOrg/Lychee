<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Album;

use App\Actions\Album\Create as AlbumCreateAction;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Factories\AlbumFactory;
use App\Http\Requests\Album\MergeAlbumsRequest;
use App\Models\User;
use App\Rules\AlbumIDRule;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;
use Tests\Unit\Http\Requests\Base\BaseRequestTest;

class MergeAlbumRequestTest extends BaseRequestTest
{
	public function testAuthorization()
	{
		$album = $this->createUserAndAlbum();

		//		$album->getBaseAlbumFactory()->setAlbum($album);

		$abstractAlbum = new AlbumFactory();
		$abstractAlbum = $abstractAlbum->findAbstractAlbumOrFail($album->id);
		Gate::shouldReceive('check')
			->times(4) // TODO:// not only the times, but make sure the right arguments are passed, but this is hard to do find with mockery
			->andReturn(true);

		$request = new MergeAlbumsRequest();
		$request->setContainer($this->app);
		$request->setRedirector($this->app['redirect']);
		$request->merge([
			RequestAttribute::PARENT_ID_ATTRIBUTE => null,
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => [$album->id],
			RequestAttribute::ALBUM_ID_ATTRIBUTE => $album->id,
			RequestAttribute::ALBUM_IDS_ATTRIBUTE . '.*' => $album->id,
		]);

		$request->validateResolved(); // hydrate the request Class with the data before authorizing . Fighting the framework a bit

		$this->assertTrue($request->authorize());
	}

	public function testRules(): void
	{
		$request = new MergeAlbumsRequest();

		$rules = $request->rules();

		$expectedRuleMap = [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new AlbumIDRule(false)],		];

		$this->assertCount(count($expectedRuleMap), $rules);

		foreach ($expectedRuleMap as $key => $value) {
			$this->assertEquals($value, $rules[$key]);
		}
	}

	private function createUserAndAlbum(): \App\Models\Album
	{
		// TOODO:// refactor the MergeAlbumRequest to use the AlbumFactory or a Repo so that the test can be written without talking to DB
		$user = new User();
		$user->username = 'testUser' . uniqid();
		$user->password = 'testPassword';
		$user->save();

		$album_create = new AlbumCreateAction($user->id);

		$album = $album_create->create('Test Album' . uniqid(), null);

		return $album;
	}
}