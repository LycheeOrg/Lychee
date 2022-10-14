<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @see \Laragear\WebAuthn\Models\WebAuthnCredential
 */
class CreateWebauthnCredentials extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
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
	 *
	 * @return void
	 */
	public function down(): void
	{
		Schema::dropIfExists('webauthn_credentials');
	}

	/**
	 * Generate the default blueprint for the WebAuthn credentials table.
	 *
	 * @param \Illuminate\Database\Schema\Blueprint $table
	 *
	 * @return void
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
}