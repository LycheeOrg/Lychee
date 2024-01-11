<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	public const AMAZON = 'amazon_id';
	public const APPLE = 'apple_id';
	public const FACEBOOK = 'facebook_id';
	public const GITHUB = 'github_id';
	public const GOOGLE = 'google_id';
	public const MASTODON = 'mastodon_id';
	public const MICROSOFT = 'microsoft_id';
	public const NEXTCLOUD = 'nextcloud_id';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('users', function (Blueprint $table) {
			$table->string(self::AMAZON)->nullable(true)->default(null)->after('remember_token');
			$table->string(self::APPLE)->nullable(true)->default(null)->after('remember_token');
			$table->string(self::FACEBOOK)->nullable(true)->default(null)->after('remember_token');
			$table->string(self::GITHUB)->nullable(true)->default(null)->after('remember_token');
			$table->string(self::GOOGLE)->nullable(true)->default(null)->after('remember_token');
			$table->string(self::MASTODON)->nullable(true)->default(null)->after('remember_token');
			$table->string(self::MICROSOFT)->nullable(true)->default(null)->after('remember_token');
			$table->string(self::NEXTCLOUD)->nullable(true)->default(null)->after('remember_token');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::disableForeignKeyConstraints();
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(self::AMAZON);
		});
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(self::APPLE);
		});
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(self::FACEBOOK);
		});
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(self::GITHUB);
		});
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(self::GOOGLE);
		});
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(self::MASTODON);
		});
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(self::MICROSOFT);
		});
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(self::NEXTCLOUD);
		});
		Schema::enableForeignKeyConstraints();
	}
};
