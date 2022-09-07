<?php

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWebAuthnTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('configs')) {
			Configs::where('key', '=', 'username')->orWhere('key', '=', 'password')->update(['type_range' => 'string_required']);
		}
		Schema::dropIfExists('web_authn_credentials');
	}
}
