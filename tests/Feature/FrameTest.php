<?php

namespace Tests\Feature;

use App\Configs;
use Tests\TestCase;

class FrameTest extends TestCase
{
	public function testFrame_0()
	{
		// save initial value
		$init_config_value = Configs::get_value('Mod_Frame');

		// set to 0
		Configs::set('Mod_Frame', '0');
		$this->assertEquals(Configs::get_value('Mod_Frame'), '0');

		// check redirection
		$response = $this->get('/frame');
		$response->assertStatus(302);
		$response->assertRedirect('/');

		// check error
		$response = $this->post('/api/Frame::getSettings');
		$response->assertExactJson(['Error: Frame is not enabled']);

		// set back to initial value
		Configs::set('Mod_Frame', $init_config_value);
	}

	public function testFrame_1()
	{
		// save initial value
		$init_config_value = Configs::get_value('Mod_Frame');

		// set to 1
		Configs::set('Mod_Frame', '1');
		$this->assertEquals(Configs::get_value('Mod_Frame'), '1');

		// check no redirection
		$response = $this->get('/frame');
		$response->assertStatus(200);
		$response->assertViewIs('frame');

		// check refresh returned
		$response = $this->post('/api/Frame::getSettings');
		$response->assertJsonMissingExact(['Error: Frame is not enabled']);
		$ret = ['refresh' => Configs::get_value('Mod_Frame_refresh') * 1000];
		$response->assertExactJson($ret);

		// set back to initial value
		Configs::set('Mod_Frame', $init_config_value);
	}
}
