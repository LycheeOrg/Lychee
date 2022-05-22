<?php

namespace Tests\Feature;

use App\LDAP\FixedArray;
use Tests\TestCase;

class FixedArrayTest extends TestCase
{
	public const entries = ['user', 'display_name', 'dn'];

	public function testFixedArray()
	{
		$FA = new FixedArray(self::entries);
		$this->assertEquals($FA->get_properties(), self::entries);

		$this->assertEquals($FA->count(), count(self::entries));
		$this->assertEquals(count($FA), count(self::entries));
		$this->assertEquals($FA->count_set(), 0);

		$FA->user = 'username';
		$this->assertEquals($FA->count_set(), 1);
		$this->assertEquals($FA['user'], 'username');

		$FA['display_name'] = 'full_name';
		$this->assertEquals($FA->count_set(), 2);
		$this->assertEquals($FA->display_name, 'full_name');
		$this->assertEqualsCanonicalizing($FA->toArray(), ['user' => 'username', 'display_name' => 'full_name'], 'test A');

		$FB = new FixedArray(self::entries);
		$FB->fromArray(['user' => 'username', 'display_name' => 'full_name']);
		$this->assertEquals($FB->count_set(), 2);
		$this->assertEqualsCanonicalizing($FB->toArray(), $FA->toArray(), 'Test B');
		$FB->offsetUnset('user');
		$this->assertEquals($FB->count_set(), 1);
		$this->assertEqualsCanonicalizing($FB->toArray(), ['display_name' => 'full_name'], 'Test C');

		$this->assertTrue($FA->property_exists('user'));
		$this->assertTrue(isset($FA->user));
		$this->assertFalse(isset($FA->nouser));
		$this->assertFalse(isset($FA->dn));
		foreach ($FA as $prop => $value) {
			$this->assertTrue($FA[$prop] === $value);
		}
	}

	public function testFixedArrayExcept1()
	{
		$FA = new FixedArray(self::entries);
		$this->expectException(\ErrorException::class);
		$FA->nouser = 'username';
	}

	public function testFixedArrayExcept2()
	{
		$FA = new FixedArray(self::entries);
		$this->expectException(\ErrorException::class);
		$FA['nouser'] = 'username';
	}

	public function testFixedArrayExcept3()
	{
		$FA = new FixedArray(self::entries);
		$this->expectException(\ErrorException::class);
		$FA->offsetunset('nouser');
	}

	public function testFixedArrayExcept4()
	{
		$FA = new FixedArray(self::entries);
		$this->expectException(\ErrorException::class);
		$V = $FA['nouser'];
	}

	public function testFixedArrayExcept5()
	{
		$FA = new FixedArray(self::entries);
		$this->expectException(\ErrorException::class);
		$FA->fromArray(['nouser' => 'test']);
	}
}

