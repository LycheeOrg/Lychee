<?php

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConfigLdapParams extends Migration
{
	/**
	 * We set up the configuration for the public LDAP Server
	 * See https://www.forumsys.com/2022/05/10/online-ldap-test-server/.
	 *
	 * After enabling the LDAP authentication with ldap_enabled set to 1
	 * username: gauss and password: password can be used.
	 *
	 * /**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
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
				'value' => 'ldap.example.tld',
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
				'key' => 'ldap_user_tree',
				'value' => 'dc=example,dc=com',
				'cat' => 'LDAP',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'LDAP user tree',
			],
			[
				'key' => 'ldap_user_filter',
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
				'key' => 'ldap_bind_dn',
				'value' => 'cn=read-only-admin,dc=example,dc=com',
				'cat' => 'LDAP',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'LDAP bind dn',
			],
			[
				'key' => 'ldap_bind_pw',
				'value' => 'password',
				'cat' => 'LDAP',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'LDAP bind password',
			],
			[
				'key' => 'ldap_user_key',
				'value' => 'uid',
				'cat' => 'LDAP',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'LDAP user key',
			],
			[
				'key' => 'ldap_user_scope',
				'value' => 'sub',
				'cat' => 'LDAP',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'LDAP user scope',
			],
			[
				'key' => 'ldap_start_tls',
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
	public function down(): void
	{
		Configs::query()->where('key', '=', 'ldap_enabled')->delete();
		Configs::query()->where('key', '=', 'ldap_server')->delete();
		Configs::query()->where('key', '=', 'ldap_port')->delete();
		Configs::query()->where('key', '=', 'ldap_user_tree')->delete();
		Configs::query()->where('key', '=', 'ldap_user_filter')->delete();
		Configs::query()->where('key', '=', 'ldap_version')->delete();
		Configs::query()->where('key', '=', 'ldap_bind_dn')->delete();
		Configs::query()->where('key', '=', 'ldap_bind_pw')->delete();
		Configs::query()->where('key', '=', 'ldap_user_key')->delete();
		Configs::query()->where('key', '=', 'ldap_user_scope')->delete();
		Configs::query()->where('key', '=', 'ldap_starttls')->delete();
		Configs::query()->where('key', '=', 'ldap_referrals')->delete();
		Configs::query()->where('key', '=', 'ldap_deref')->delete();
		Configs::query()->where('key', '=', 'ldap_cn')->delete();
		Configs::query()->where('key', '=', 'ldap_mail')->delete();
	}
}
