<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UTCBasedTimes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Component\Uid\Uuid;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\TrustPath\EmptyTrustPath;

/**
 * App\Models\WebAuthnCredential.
 *
 * A security key registered by a user (Feature 107, FR-107-08). Binary values
 * are stored as base64url without padding: `id` (credential id), `user_handle`
 * and `public_key` (a COSE key, encrypted with `APP_KEY`).
 *
 * @property string                $id
 * @property string                $authenticatable_type
 * @property int                   $authenticatable_id
 * @property User                  $authenticatable
 * @property string                $user_handle
 * @property string|null           $alias
 * @property int|null              $counter
 * @property bool|null             $backup_eligible
 * @property bool|null             $backup_status
 * @property bool|null             $uv_initialized
 * @property string                $rp_id
 * @property string                $origin
 * @property list<string>|null     $transports
 * @property string|null           $aaguid
 * @property string                $public_key
 * @property string                $attestation_format
 * @property array<int,mixed>|null $certificates
 * @property Carbon|null           $disabled_at
 * @property Carbon                $created_at
 * @property Carbon                $updated_at
 */
class WebAuthnCredential extends Model
{
	use UTCBasedTimes;
	use ThrowsConsistentExceptions;

	private const NIL_AAGUID = '00000000-0000-0000-0000-000000000000';
	/** CBOR major type of a map, which every COSE key is. */
	private const CBOR_MAP_MAJOR_TYPE = 5;

	protected $table = 'webauthn_credentials';
	public $incrementing = false;
	protected $keyType = 'string';

	protected $fillable = [
		'id',
		'user_handle',
		'alias',
		'counter',
		'backup_eligible',
		'backup_status',
		'uv_initialized',
		'rp_id',
		'origin',
		'transports',
		'aaguid',
		'public_key',
		'attestation_format',
	];

	protected $casts = [
		'authenticatable_id' => 'integer',
		'counter' => 'integer',
		'backup_eligible' => 'boolean',
		'backup_status' => 'boolean',
		'uv_initialized' => 'boolean',
		'transports' => 'array',
		'certificates' => 'array',
		'public_key' => 'encrypted',
		'disabled_at' => 'datetime',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
	];

	protected $hidden = [
		'public_key',
		'user_handle',
	];

	/**
	 * @return MorphTo<Model,$this>
	 */
	public function authenticatable(): MorphTo
	{
		return $this->morphTo();
	}

	/**
	 * Whether `public_key` decrypts to a COSE key.
	 *
	 * False for a row encrypted with another `APP_KEY` or left in Laragear's PEM
	 * form by the migration (FR-107-09): its owner has to register it again.
	 */
	public function hasReadablePublicKey(): bool
	{
		try {
			$key = Base64UrlSafe::decodeNoPadding($this->public_key);
		} catch (\Throwable) {
			return false;
		}

		return $key !== '' && (ord($key[0]) >> 5) === self::CBOR_MAP_MAJOR_TYPE;
	}

	/**
	 * The record `web-auth/webauthn-lib` validates an assertion against.
	 *
	 * Attestation type and trust path are only used at registration, so they
	 * are not stored (N-107-02).
	 */
	public function toCredentialRecord(): CredentialRecord
	{
		return CredentialRecord::create(
			publicKeyCredentialId: Base64UrlSafe::decodeNoPadding($this->id),
			type: PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
			transports: $this->transports ?? [],
			attestationType: 'none',
			trustPath: EmptyTrustPath::create(),
			aaguid: Uuid::fromString($this->aaguid ?? self::NIL_AAGUID),
			credentialPublicKey: Base64UrlSafe::decodeNoPadding($this->public_key),
			userHandle: Base64UrlSafe::decodeNoPadding($this->user_handle),
			counter: $this->counter ?? 0,
			backupEligible: $this->backup_eligible,
			backupStatus: $this->backup_status,
			uvInitialized: $this->uv_initialized,
		);
	}

	/**
	 * Keep what a successful login updated on the record (counter, backup state).
	 */
	public function fillFromCredentialRecord(CredentialRecord $record): void
	{
		$this->counter = $record->counter;
		$this->backup_eligible = $record->backupEligible;
		$this->backup_status = $record->backupStatus;
		$this->uv_initialized = $record->uvInitialized;
	}

	/**
	 * Descriptor for `allowCredentials` / `excludeCredentials`.
	 */
	public function toDescriptor(): PublicKeyCredentialDescriptor
	{
		return PublicKeyCredentialDescriptor::create(
			PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
			Base64UrlSafe::decodeNoPadding($this->id),
			$this->transports ?? [],
		);
	}
}
