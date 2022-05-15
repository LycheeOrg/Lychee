<?php

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

class ConfigLdapParams extends Migration
{
	/**
	 * We setup the configuration for the public LDAP Server
	 * See https://www.forumsys.com/2022/05/10/online-ldap-test-server/.
	 *
	 * After enabling the LDAP authentication with ldap_enabled set to 1
	 * username: gauss and passward: password can be used.
	 *
	 * /**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('configs')->insert([
			[
				'key' => 'ldap_enabled',
				'value' => '0',
				'cat' => 'LDAP',
				'type_range' => '0|1',
				'confidentiality' => '0',
				'description' => 'LDAP login provider enabled',
			],
			[
				'key' => 'ldap_server',
				'value' => 'ldap.forumsys.com',
				'cat' => 'LDAP',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'LDAP server name',
			],
			[
				'key' => 'ldap_port',
				'value' => '389',
				'cat' => 'LDAP',
				'type_range' => 'int',
				'confidentiality' => '0',
				'description' => 'LDAP server port',
			],
			[
				'key' => 'ldap_usertree',
				'value' => 'dc=example,dc=com',
				'cat' => 'LDAP',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'LDAP user tree',
			],
			[
				'key' => 'ldap_userfilter',
				'value' => '(uid=%{user})',
				'cat' => 'LDAP',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'LDAP user filter',
			],
			[
				'key' => 'ldap_version',
				'value' => '3',
				'cat' => 'LDAP',
				'type_range' => 'int',
				'confidentiality' => '0',
				'description' => 'LDAP protocol version',
			],
			[
				'key' => 'ldap_binddn',
				'value' => 'cn=read-only-admin,dc=example,dc=com',
				'cat' => 'LDAP',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'LDAP bind dn',
			],
			[
				'key' => 'ldap_bindpw',
				'value' => 'password',
				'cat' => 'LDAP',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'LDAP bind password',
			],
			[
				'key' => 'ldap_userkey',
				'value' => 'uid',
				'cat' => 'LDAP',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'LDAP user key',
			],
			[
				'key' => 'ldap_userscope',
				'value' => 'sub',
				'cat' => 'LDAP',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'LDAP user scope',
			],
			[
				'key' => 'ldap_groupscope',
				'value' => 'sub',
				'cat' => 'LDAP',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'LDAP group scope',
			],
			[
				'key' => 'ldap_starttls',
				'value' => '0',
				'cat' => 'LDAP',
				'type_range' => '0|1',
				'confidentiality' => '0',
				'description' => 'LDAP use STARTTLS protocol',
			],
			[
				'key' => 'ldap_referrals',
				'value' => '-1',
				'cat' => 'LDAP',
				'type_range' => 'signed_int',
				'confidentiality' => '0',
				'description' => 'LDAP option referrals',
			],
			[
				'key' => 'ldap_deref',
				'value' => '0',
				'cat' => 'LDAP',
				'type_range' => '0|1',
				'confidentiality' => '0',
				'description' => 'LDAP option deref',
			],
			[
				'key' => 'ldap_cn',
				'value' => 'cn',
				'cat' => 'LDAP',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'LDAP common name',
			],
			[
				'key' => 'ldap_mail',
				'value' => 'mail',
				'cat' => 'LDAP',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'LDAP mail entry',
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Configs::where('key', '=', 'ldap_enabled')->delete();
		Configs::where('key', '=', 'ldap_server')->delete();
		Configs::where('key', '=', 'ldap_port')->delete();
		Configs::where('key', '=', 'ldap_usertree')->delete();
		Configs::where('key', '=', 'ldap_userfilter')->delete();
		Configs::where('key', '=', 'ldap_version')->delete();
		Configs::where('key', '=', 'ldap_binddn')->delete();
		Configs::where('key', '=', 'ldap_bindpw')->delete();
		Configs::where('key', '=', 'ldap_userkey')->delete();
		Configs::where('key', '=', 'ldap_userscope')->delete();
		Configs::where('key', '=', 'ldap_groupscope')->delete();
		Configs::where('key', '=', 'ldap_starttls')->delete();
		Configs::where('key', '=', 'ldap_referrals')->delete();
		Configs::where('key', '=', 'ldap_deref')->delete();
		Configs::where('key', '=', 'ldap_cn')->delete();
		Configs::where('key', '=', 'ldap_mail')->delete();
	}
}
