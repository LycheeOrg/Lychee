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

class FrameTest extends TestCase
{
	public function testFrame0()
	{
		// save initial value
		$init_config_value = Configs::get_value('Mod_Frame');

		// set to 0
		Configs::set('Mod_Frame', '0');
		static::assertEquals('0', Configs::get_value('Mod_Frame'));

		// check redirection
		$response = $this->get('/frame');
		$response->assertStatus(302);
		$response->assertRedirect('/');

		// check error
		$response = $this->postJson('/api/Frame::getSettings');
		$response->assertStatus(412);
		$response->assertJson([
			'message' => 'Frame is not enabled',
			'exception' => 'App\\Exceptions\\ConfigurationException',
		]);

		// set back to initial value
		Configs::set('Mod_Frame', $init_config_value);
	}

	public function testFrame1()
	{
		// save initial value
		$init_config_value = Configs::get_value('Mod_Frame');

		// set to 1
		Configs::set('Mod_Frame', '1');
		static::assertEquals('1', Configs::get_value('Mod_Frame'));

		// check no redirection
		$response = $this->get('/frame');
		$response->assertOk();
		$response->assertViewIs('frame');

		// check refresh returned
		$response = $this->postJson('/api/Frame::getSettings');
		$response->assertJsonMissingExact(['Error: Frame is not enabled']);
		$ret = ['refresh' => Configs::get_value('Mod_Frame_refresh') * 1000];
		$response->assertExactJson($ret);

		// set back to initial value
		Configs::set('Mod_Frame', $init_config_value);
	}
}
