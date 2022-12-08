<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebAuthnTables extends Migration
{
	public const DELETED_AT = 'disabled_at';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		defined('STRING') or define('STRING', 'string');

		Schema::create('web_authn_credentials', function (Blueprint $table) {
			$table->string('id', 255);

			// Change accordingly for your users table if you need to.
			$table->unsignedBigInteger('user_id');

			$table->string('name')->nullable();
			$table->string('type', 16);
			$table->json('transports');
			$table->json('attestation_type');
			$table->json('trust_path');
			$table->uuid('aaguid');
			$table->binary('public_key');
			$table->unsignedInteger('counter')->default(0);

			// This saves the external "ID" that identifies the user. We use UUID default
			// since it's very straightforward. You can change this for a plain string.
			// It must be nullable because those old U2F keys do not use user handle.
			$table->uuid('user_handle')->nullable();

			$table->timestamps();
			$table->softDeletes(self::DELETED_AT);
			DB::table('configs')->where('key', '=', 'username')->orWhere('key', '=', 'password')->update(['type_range' => STRING]);

			$table->primary(['id', 'user_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		defined('STRING_REQ') or define('STRING_REQ', 'string_required');

		if (Schema::hasTable('configs')) {
			DB::table('configs')->where('key', '=', 'username')->orWhere('key', '=', 'password')->update(['type_range' => STRING_REQ]);
		}
		Schema::dropIfExists('web_authn_credentials');
	}
}
