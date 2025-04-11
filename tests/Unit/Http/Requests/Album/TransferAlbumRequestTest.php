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
use App\Http\Requests\Album\TransferAlbumRequest;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use App\Rules\IntegerIDRule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use LycheeVerify\Contract\VerifyInterface;
use Tests\AbstractTestCase;

class TransferAlbumRequestTest extends AbstractTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		$mockVerify = $this->createMock(VerifyInterface::class);
		App::instance(VerifyInterface::class, $mockVerify); // VerifyInterface is talking to DB & that is not needed for Request classes
	}

	public function testAuthorization()
	{
		Gate::shouldReceive('check')
			->with(AlbumPolicy::CAN_TRANSFER, [AbstractAlbum::class, null])
			->andReturn(true);

		$request = new TransferAlbumRequest();
		$request->setContainer($this->app);
		$request->setRedirector($this->app['redirect']);
		$request->merge([
			RequestAttribute::ALBUM_ID_ATTRIBUTE => 'ePN3Y_kA16KtZGXmxv-kdBrg',
			RequestAttribute::USER_ID_ATTRIBUTE => 1,
		]);

		$this->assertTrue($request->authorize());
	}

	public function testRules(): void
	{
		$request = new TransferAlbumRequest();

		$rules = $request->rules();

		$expectedRuleMap = [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			RequestAttribute::USER_ID_ATTRIBUTE => ['required', new IntegerIDRule(false)],
		];
		$this->assertCount(count($expectedRuleMap), $rules); // only validating the first 7 rules & the GRANTS_UPLOAD_ATTRIBUTE is tested afterwards

		foreach ($expectedRuleMap as $key => $value) {
			$this->assertEquals($value, $rules[$key]);
		}
	}
}