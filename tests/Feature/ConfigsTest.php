<?php

namespace Tests\Feature;

use App\Models\Configs;
use Tests\TestCase;

class ConfigsTest extends TestCase
{
	protected $oldconfigs = null;

	protected const TESTKEY = 'ldap_update_users';
	protected const UNKNOWNKEY = '__UNKNOWN__';
	protected const INT = 'int';
	protected const SIGNED_INT = 'signed_int';
	protected const STRING = 'string';
	protected const STRING_REQ = 'string_required';
	protected const BOOL = '0|1';
	protected const TERNARY = '0|1|2';
	protected const DISABLED = '';
	protected const LICENSE = 'license';

	protected function Configs_setUp(): void
	{
		$this->oldconfigs = Configs::get();
	}

	protected function Configs_tearDown(): void
	{
		Configs::set(self::TESTKEY, $this->oldconfigs[self::TESTKEY]);
	}

	public function testSetTypeOf()
	{
		$this->Configs_setUp();
		try {
			$v = 'string';
			$tv = Configs::set_type_of($v, self::STRING);
			$this->assertEquals($tv, $v);
			$this->assertIsString($tv);
			$tv = Configs::set_type_of($v, self::STRING_REQ);
			$this->assertEquals($tv, $v);
			$this->assertIsString($tv);
			$tv = Configs::set_type_of($v, self::DISABLED);
			$this->assertEquals($tv, $v);
			$this->assertIsString($tv);
			$tv = Configs::set_type_of($v, self::LICENSE);
			$this->assertEquals($tv, $v);
			$this->assertIsString($tv);

			$v = '12345';
			$tv = Configs::set_type_of($v, self::INT);
			$this->assertEquals($tv, $v);
			$this->assertIsInt($tv);
			$tv = Configs::set_type_of($v, self::SIGNED_INT);
			$this->assertEquals($tv, $v);
			$this->assertIsInt($tv);
			$tv = Configs::set_type_of($v, self::TERNARY);
			$this->assertEquals($tv, $v);
			$this->assertIsInt($tv);

			$v = '0';
			$tv = Configs::set_type_of($v, self::BOOL);
			$this->assertIsBool($tv);
			$this->assertEquals($tv, boolval($v));
			$v = '1';
			$tv = Configs::set_type_of($v, self::BOOL);
			$this->assertIsBool($tv);
			$this->assertEquals($tv, boolval($v));

			$v = 'string';
			$tv = Configs::set_type_of($v, self::INT);
			$this->assertIsNotInt($tv);
			$this->assertEquals($tv, $v);
		} finally {
			$this->Configs_tearDown();
		}
	}

	public function testGet()
	{
		$this->Configs_setUp();
		try {
			$values = Configs::get();
			$this->assertIsArray($values);
			$values = Configs::get();
			$this->assertIsArray($values);
		} finally {
			$this->Configs_tearDown();
		}
	}

	public function testGetValue()
	{
		$this->Configs_setUp();
		try {
			$v = '1';
			$tv = Configs::get_value(self::UNKNOWNKEY, $v);
			$this->assertEquals($tv, $v);
		} finally {
			$this->Configs_tearDown();
		}
	}

	public function testSet()
	{
		$this->Configs_setUp();
		try {
			$did_except = false;
			try {
				Configs::set(self::TESTKEY, 'string');
			} catch (\App\Exceptions\Internal\InvalidConfigOption) {
				$did_except = true;
			}
			$this->assertTrue($did_except, 'Missing Exception InvalidConfigOption');
			$v = '1';
			$this->expectException(\App\Exceptions\Internal\InvalidConfigOption::class);
			Configs::set(self::UNKNOWNKEY, $v);
		} finally {
			$this->Configs_tearDown();
		}
	}

	public function testSanity()
	{
		$this->Configs_setUp();
		try {
			$config = new Configs();
			$config->type_range = self::STRING;
			$this->assertEquals('', $config->sanity('string'));
			$config->type_range = self::STRING_REQ;
			$this->assertEquals('', $config->sanity('string'));
			$this->assertNotEquals('', $config->sanity(''));
			$config->type_range = self::INT;
			$this->assertEquals('', $config->sanity('1'));
			$this->assertNotEquals('', $config->sanity('-1'));
			$config->type_range = self::SIGNED_INT;
			$this->assertEquals('', $config->sanity('-1'));
			$this->assertNotEquals('', $config->sanity('string'));
			$config->type_range = self::BOOL;
			$this->assertEquals('', $config->sanity('0'));
			$this->assertNotEquals('', $config->sanity('2'));
			$config->type_range = self::TERNARY;
			$this->assertEquals('', $config->sanity('2'));
			$this->assertNotEquals('', $config->sanity('3'));
			$config->type_range = self::LICENSE;
			$this->assertEquals('', $config->sanity('none'));
			$this->assertNotEquals('', $config->sanity('__not_valid__'));
			$config->type_range = 'abc|def|ghi';
			$this->assertEquals('', $config->sanity('abc'));
			$this->assertNotEquals('', $config->sanity('__not_valid__'));
		} finally {
			$this->Configs_tearDown();
		}
	}

	public function testGetValueASCronSpec()
	{
		$this->Configs_setUp();
		try {
			Configs::set(self::TESTKEY, 0);
			$this->assertEquals('', Configs::get_value_as_cron_spec(self::TESTKEY, 0), 'Should be empty');
			Configs::set(self::TESTKEY, 1);
			$this->assertEquals('*/1 * * * *', Configs::get_value_as_cron_spec(self::TESTKEY, 1));
			Configs::set(self::TESTKEY, 59);
			$this->assertEquals('*/59 * * * *', Configs::get_value_as_cron_spec(self::TESTKEY, 59));
			Configs::set(self::TESTKEY, 60);
			$this->assertEquals('0 */1 * * *', Configs::get_value_as_cron_spec(self::TESTKEY, 60));
			Configs::set(self::TESTKEY, 65);
			$this->assertEquals('5 */1 * * *', Configs::get_value_as_cron_spec(self::TESTKEY, 65));
			Configs::set(self::TESTKEY, 24 * 60 - 1);
			$this->assertEquals('59 */23 * * *', Configs::get_value_as_cron_spec(self::TESTKEY, 24 * 60 - 1));
			Configs::set(self::TESTKEY, 24 * 60);
			$this->assertEquals('0 0 * * *', Configs::get_value_as_cron_spec(self::TESTKEY, 24 * 60));
			Configs::set(self::TESTKEY, 24 * 60 + 5);
			$this->assertEquals('5 0 * * *', Configs::get_value_as_cron_spec(self::TESTKEY, 24 * 60 + 5));
			Configs::set(self::TESTKEY, 24 * 60 + 65);
			$this->assertEquals('5 1 * * *', Configs::get_value_as_cron_spec(self::TESTKEY, 24 * 60 + 65));
		} finally {
			$this->Configs_tearDown();
		}
	}
}
