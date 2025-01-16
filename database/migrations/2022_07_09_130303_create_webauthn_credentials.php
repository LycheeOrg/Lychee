<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @see \Laragear\WebAuthn\Models\WebAuthnCredential
 */
return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::dropIfExists('web_authn_credentials');
		Schema::create('webauthn_credentials', static function (Blueprint $table): void {
			static::defaultBlueprint($table);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('webauthn_credentials');
		Schema::create('web_authn_credentials', function (Blueprint $table): void {
			static::oldTable($table);
		});
	}

	/**
	 * Generate the default blueprint for the WebAuthn credentials table.
	 *
	 * @param \Illuminate\Database\Schema\Blueprint $table
	 */
	protected static function defaultBlueprint(Blueprint $table): void
	{
		$table->string('id')->primary();

		$table->morphs('authenticatable', 'webauthn_user_index');

		// This is the user UUID that is generated automatically when a credential for the
		// given user is created. If a second credential is created, this UUID is queried
		// and then copied on top of the new one, this way the real User ID doesn't change.
		$table->uuid('user_id');

		// The app may allow the user to name or rename a credential to a friendly name,
		// like "John's iPhone" or "Office Computer".
		$table->string('alias')->nullable();

		// Allows to detect cloned credentials when the assertion does not have this same counter.
		$table->unsignedBigInteger('counter')->nullable();
		// Who created the credential. Should be the same reported by the Authenticator.
		$table->string('rp_id');
		// Where the credential was created. Should be the same reported by the Authenticator.
		$table->string('origin');
		$table->json('transports')->nullable();
		$table->uuid('aaguid')->nullable(); // GUID are essentially UUID

		// This is the public key the credential uses to verify the challenges.
		$table->text('public_key');
		// The attestation of the public key.
		$table->string('attestation_format')->default('none');
		// This would hold the certificate chain for other different attestation formats.
		$table->json('certificates')->nullable();

		// A way to disable the credential without deleting it.
		$table->dateTime('disabled_at')->nullable();
		$table->dateTime('created_at')->nullable(false);
		$table->dateTime('updated_at')->nullable(false);
	}

	/**
	 * Generate the default blueprint for the WebAuthn credentials table.
	 *
	 * @param \Illuminate\Database\Schema\Blueprint $table
	 */
	protected static function oldTable(Blueprint $table): void
	{
		$table->string('id', 255);
		$table->dateTime('created_at', 6)->nullable(false);
		$table->dateTime('updated_at', 6)->nullable(false);
		$table->dateTime('disabled_at', 6)->nullable(true);
		$table->unsignedInteger('user_id')->nullable(false);
		$table->string('name')->nullable();
		$table->string('type', 16);
		$table->json('transports');
		$table->json('attestation_type');
		$table->json('trust_path');
		$table->uuid('aaguid');
		$table->binary('public_key');
		$table->unsignedInteger('counter')->default(0);
		$table->uuid('user_handle')->nullable();
		// Indices
		$table->primary(['id', 'user_id']);
		$table->foreign('user_id')
			->references('id')->on('users')
			->cascadeOnDelete();
	}
};