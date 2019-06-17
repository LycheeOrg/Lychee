<?php

namespace Tests\Feature;

use App\Configs;
use Tests\TestCase;

class DemoTest extends TestCase
{
	/**
	 * Check that the demo page is not available
	 * if not enabled in the advanced config.
	 */
	public function testDemo_0()
	{
		// save initial value
		$init_config_value = Configs::get_value('gen_demo_js');

		// set to 0
		Configs::set('gen_demo_js', '0');
		$this->assertEquals(Configs::get_value('gen_demo_js'), '0');

		// check redirection
		$response = $this->get('/demo');
		$response->assertStatus(302);
		$response->assertRedirect('/');

		// set back to initial value
		Configs::set('gen_demo_js', $init_config_value);
	}

	/**
	 * Check that the demo page is available
	 * if enabled in the advanced config.
	 */
	public function testDemo_1()
	{
		// save initial value
		$init_config_value = Configs::get_value('gen_demo_js');

		// set to 0
		Configs::set('gen_demo_js', '1');
		$this->assertEquals(Configs::get_value('gen_demo_js'), '1');

		// check redirection
		$response = $this->get('/demo');
		$response->assertStatus(200);
		$response->assertViewIs('demo');

		// set back to initial value
		Configs::set('gen_demo_js', $init_config_value);
	}
}
