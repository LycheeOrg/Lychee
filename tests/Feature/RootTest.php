<?php

/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use Tests\TestCase;

class RootTest extends TestCase
{
	/**
	 * Test album functions.
	 *
	 * @return void
	 */
	public function testRoot()
	{
		exec('php index.php 2>&1', $return);
		$return = implode('', $return);
		$this->assertStringContainsString('This is the root directory and it MUST NOT BE PUBLICALLY ACCESSIBLE', $return);
	}
}
