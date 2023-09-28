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

namespace Tests\Livewire;

use App\Exceptions\Internal\LycheeLogicException;
use App\Livewire\Traits\UseWireable;
use Tests\AbstractTestCase;

class WireableTest extends AbstractTestCase
{
	public function testWirableDehydrate() : void {
		$trait = new class {
            use UseWireable;
			public $num;

			public function __construct()
			{
				$this->num = new class {};
			}
		};

		$this->assertThrows(fn () => $trait->toLivewire(), LycheeLogicException::class);
	}

	public function testWirableHydrate() : void {
		$trait = new class {
            use UseWireable;
		};

		$this->assertThrows(fn () => $trait->fromLivewire(""), LycheeLogicException::class);
	}
}