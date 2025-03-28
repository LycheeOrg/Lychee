<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\Album\DeleteTrackRequest;
use App\Http\Requests\Album\GetAlbumRequest;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use LycheeVerify\Contract\VerifyInterface;
use Tests\AbstractTestCase;

class GetAlbumRequestTest extends AbstractTestCase
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
			->with(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, null])
			->andReturn(true);

		$request = new GetAlbumRequest();
		$request->merge([RequestAttribute::PARENT_ID_ATTRIBUTE => null]);

		$this->assertTrue($request->authorize());
	}

	public function testRules(): void
	{
		$request = new DeleteTrackRequest();

		$rules = $request->rules();

		$expectedRuleMap = [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
		];

		$this->assertCount(count($expectedRuleMap), $rules);

		foreach ($expectedRuleMap as $key => $value) {
			$this->assertEquals($value, $rules[$key]);
		}
	}
}