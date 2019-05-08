<?php
/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use Tests\TestCase;

class FailTest extends TestCase
{
	/**
	 * Fail
	 *
	 * @return void
	 */
	public function test_fail()
	{
		$this->assertTrue(false);

	}

}
