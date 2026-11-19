<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use function Safe\preg_match;

/**
 * Feature 107 (FR-107-08, FR-107-09, ADR-107-02): `webauthn_credentials` gets
 * what `web-auth/webauthn-lib` needs to rebuild a credential record.
 *
 * Laragear's `user_id` (a UUID column) becomes `user_handle`, the base64url of
 * the user handle bytes. Laragear sent the UUID as 32 hex characters in
 * `user.id` and the front-end helper base64url-decoded that string, so the
 * authenticators hold `base64url_decode(hex)` and its base64url is the hex
 * string itself: the value only loses its dashes (PostgreSQL adds them).
 */
return new class() extends Migration {
	private const TABLE = 'webauthn_credentials';

	/**
	 * Run the migrations.
	 *
	 * Every step is skipped when it is already done: MySQL commits each DDL
	 * statement on its own, so a run cut short can be started again.
	 */
	public function up(): void
	{
		$this->addMissingColumn('user_handle', fn (Blueprint $table) => $table->string('user_handle', 128)->nullable()->after('authenticatable_id'));
		$this->addMissingColumn('backup_eligible', fn (Blueprint $table) => $table->boolean('backup_eligible')->nullable()->after('counter'));
		$this->addMissingColumn('backup_status', fn (Blueprint $table) => $table->boolean('backup_status')->nullable()->after('backup_eligible'));
		$this->addMissingColumn('uv_initialized', fn (Blueprint $table) => $table->boolean('uv_initialized')->nullable()->after('backup_status'));
		if (!Schema::hasColumn(self::TABLE, 'user_id')) {
			return;
		}

		foreach (DB::table(self::TABLE)->select(['id', 'user_id'])->lazyById() as $row) {
			DB::table(self::TABLE)->where('id', '=', $row->id)->update(['user_handle' => $this->toUserHandle(strval($row->user_id))]);
		}

		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->string('user_handle', 128)->nullable(false)->change();
			$table->dropColumn('user_id');
		});
	}

	/**
	 * Reverse the migrations, with the same resumable steps as `up()`.
	 *
	 * A handle created after the upgrade (32 random bytes) has no UUID form: such a
	 * credential gets a new UUID and loses usernameless login under Laragear.
	 */
	public function down(): void
	{
		$this->addMissingColumn('user_id', fn (Blueprint $table) => $table->uuid('user_id')->nullable()->after('authenticatable_id'));
		if (!Schema::hasColumn(self::TABLE, 'user_handle')) {
			return;
		}

		foreach (DB::table(self::TABLE)->select(['id', 'user_handle'])->lazyById() as $row) {
			DB::table(self::TABLE)->where('id', '=', $row->id)->update(['user_id' => $this->toUserId(strval($row->user_handle))]);
		}

		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->uuid('user_id')->nullable(false)->change();
			$table->dropColumn(['user_handle', 'backup_eligible', 'backup_status', 'uv_initialized']);
		});
	}

	/**
	 * @param Closure(Blueprint):mixed $definition
	 */
	private function addMissingColumn(string $column, Closure $definition): void
	{
		if (!Schema::hasColumn(self::TABLE, $column)) {
			Schema::table(self::TABLE, $definition);
		}
	}

	/**
	 * Laragear `user_id` (dashed or plain hex UUID) to the stored user handle.
	 */
	public function toUserHandle(string $user_id): string
	{
		return strtolower(str_replace('-', '', $user_id));
	}

	/**
	 * Stored user handle back to a Laragear `user_id`, as the plain hex Laragear wrote.
	 * PostgreSQL keeps it in its native `uuid` form (dashed), which Laragear reads
	 * the same way (`Uuid::fromString()`).
	 */
	public function toUserId(string $user_handle): string
	{
		return preg_match('/^[0-9a-f]{32}$/', $user_handle) === 1 ? $user_handle : str_replace('-', '', strval(Str::uuid()));
	}
};
