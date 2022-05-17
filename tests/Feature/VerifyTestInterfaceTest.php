<?php

namespace Tests\Feature;

use Tests\TestCase;

class VerifyTestInterfaceTest extends TestCase
{
	protected function _debug($myDebugVar, $label = '')
	{
		$msg = str_replace(PHP_EOL, ' ', print_r($myDebugVar, true));
		while (str_contains($msg, '  ')) {
			$msg = str_replace('  ', ' ', $msg);
		}
		error_log($label . "'" . trim($msg) . "'");
	}

	public function testVerifyTestInterface()
	{
		exec('grep test_LDAP_ * -r -l', $ret);
		$ret = implode(' ', $ret);
		$this->assertEquals(
			$ret,
			'tests/Feature/Lib/LDAPFunctionsTest.php tests/Feature/VerifyTestInterfaceTest.php tests/Feature/LDAPTest.php',
			'LDAP test interface is missused!'
		);
	}
}
