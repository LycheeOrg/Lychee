<?php

namespace Tests\Feature;

use Tests\TestCase;

class RedirectTest extends TestCase
{
	/**
	 * A basic feature test example.
	 *
	 * @return void
	 */
	public function testRedirection(): void
	{
		$response = $this->get('r/aaaaaaaaaaaaaaaaaaaaaaaa');

		$response->assertStatus(302);
		$response->assertRedirect('gallery#aaaaaaaaaaaaaaaaaaaaaaaa');

		$response = $this->get('r/aaaaaaaaaaaaaaaaaaaaaaaa/bbbbbbbbbbbbbbbbbbbbbbbb');

		$response->assertStatus(302);
		$response->assertRedirect('gallery#aaaaaaaaaaaaaaaaaaaaaaaa/bbbbbbbbbbbbbbbbbbbbbbbb');
	}
}
