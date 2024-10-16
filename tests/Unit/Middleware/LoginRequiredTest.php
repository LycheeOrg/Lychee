<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Middleware;

use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\UnauthenticatedException;
use App\Http\Middleware\LoginRequired;
use App\Models\Configs;
use Illuminate\Http\Request;
use Tests\AbstractTestCase;

class LoginRequiredTest extends AbstractTestCase
{
	public function testExceptionLoginRequired(): void
	{
		Configs::where('key', 'login_required')->update(['value' => '1']);
		$request = $this->mock(Request::class);
		$middleware = new LoginRequired();
		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, 'root'), UnauthenticatedException::class);
	}

	public function testExceptionLoginNotRequired(): void
	{
		Configs::where('key', 'login_required')->update(['value' => '1']);
		Configs::where('key', 'login_required_root_only')->update(['value' => '1']);
		$request = $this->mock(Request::class);
		$middleware = new LoginRequired();
		$this->assertEquals(1, $middleware->handle($request, fn () => 1, 'album'));
	}

	public function testExceptionWrongParam(): void
	{
		Configs::where('key', 'login_required')->update(['value' => '1']);
		$request = $this->mock(Request::class);

		$middleware = new LoginRequired();
		$this->assertThrows(fn () => $middleware->handle($request, fn () => 1, 'nope'), LycheeInvalidArgumentException::class);
	}
}