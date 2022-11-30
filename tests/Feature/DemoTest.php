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

namespace Tests\Feature;

use App\Models\Configs;
use Tests\TestCase;

class DemoTest extends TestCase
{
	/**
	 * Check that the demo page is not available
	 * if not enabled in the advanced config.
	 */
	public function testDemo0(): void
	{
		// save initial value
		$init_config_value = Configs::getValue('gen_demo_js');

		// set to 0
		Configs::set('gen_demo_js', '0');
		static::assertEquals('0', Configs::getValue('gen_demo_js'));

		// check redirection
		$response = $this->get('/demo');
		$this->assertStatus($response, 302);
		$response->assertRedirect('/');

		// set back to initial value
		Configs::set('gen_demo_js', $init_config_value);
	}

	/**
	 * Check that the demo page is available
	 * if enabled in the advanced config.
	 */
	public function testDemo1()
	{
		// save initial value
		$init_config_value = Configs::getValue('gen_demo_js');

		// set to 0
		Configs::set('gen_demo_js', '1');
		static::assertEquals('1', Configs::getValue('gen_demo_js'));

		// check redirection
		$response = $this->get('/demo');
		$this->assertOk($response);
		$response->assertViewIs('demo');

		// set back to initial value
		Configs::set('gen_demo_js', $init_config_value);
	}
}
