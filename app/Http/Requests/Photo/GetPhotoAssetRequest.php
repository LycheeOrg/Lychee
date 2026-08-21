<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\SizeVariantType;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use App\Policies\PhotoPolicy;
use App\Repositories\ConfigManager;
use App\Rules\RandomIDRule;
use App\Rules\SizeVariantTypeNameRule;
use App\Services\TemporaryLinkSigner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Request for GET /api/v3/Photo/{photo_id}/Asset/{size_variant} (Feature 056).
 *
 * Resolves the target Photo and SizeVariant, validates the optional
 * temporary-link signature (FR-056-04/05), then authorizes against
 * PhotoPolicy::CAN_SEE (thumbnail-class variants) or
 * PhotoPolicy::CAN_ACCESS_FULL_PHOTO (full-resolution variants).
 */
class GetPhotoAssetRequest extends BaseApiRequest implements HasPhoto
{
	use HasPhotoTrait;

	private const TIMESTAMP_HEADER = 'X-Timestamp';
	private const MAC_HEADER = 'X-Mac';

	/** Internal validation-bag keys the two headers are merged into (see prepareForValidation()). */
	private const TIMESTAMP_ATTRIBUTE = 'temporary_link_timestamp';
	private const MAC_ATTRIBUTE = 'temporary_link_mac';

	/** @var SizeVariantType[] Variants gated by PhotoPolicy::CAN_SEE rather than CAN_ACCESS_FULL_PHOTO. */
	private const THUMBNAIL_CLASS = [
		SizeVariantType::THUMB,
		SizeVariantType::THUMB2X,
		SizeVariantType::SMALL,
		SizeVariantType::SMALL2X,
		SizeVariantType::PLACEHOLDER,
	];

	private SizeVariant $size_variant;
	private SizeVariantType $size_variant_type;

	private ?int $timestamp = null;
	private ?string $mac = null;

	/**
	 * Set to true only when a required temporary-link signature is absent,
	 * malformed, invalid, expired, or in the future (FR-056-04's failure
	 * path) — i.e. when the caller failed to authenticate at all, as opposed
	 * to authenticating but being denied by PhotoPolicy.
	 * Drives {@link self::failedAuthorization()}'s 401-vs-403 choice
	 * (FR-056-05), since the inherited `Auth::check()`-based mechanism
	 * cannot express this endpoint's required mapping.
	 */
	private bool $signature_check_failed = false;

	public function sizeVariant(): SizeVariant
	{
		return $this->size_variant;
	}

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		$user = Auth::user();
		$config_manager = resolve(ConfigManager::class);

		if ($this->signatureRequired($user, $config_manager) && !$this->hasValidSignature($config_manager)) {
			$this->signature_check_failed = true;

			return false;
		}

		$ability = in_array($this->size_variant_type, self::THUMBNAIL_CLASS, true)
			? PhotoPolicy::CAN_SEE
			: PhotoPolicy::CAN_ACCESS_FULL_PHOTO;

		return Gate::check($ability, [Photo::class, $this->photo]);
	}

	/**
	 * {@inheritDoc}
	 *
	 * The inherited {@link BaseApiRequest::failedAuthorization()} keys its
	 * 401-vs-403 choice off `Auth::check()` (session state), which is wrong
	 * here: a signature-valid guest denied by PhotoPolicy needs 403
	 * (S-056-03), while a logged-in user missing a config-required signature
	 * needs 401 (S-056-10) despite having a session.
	 */
	protected function failedAuthorization(): void
	{
		throw $this->signature_check_failed ? new UnauthenticatedException() : new UnauthorizedException();
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::SIZE_VARIANT_TOKEN_ATTRIBUTE => ['required', 'string', new SizeVariantTypeNameRule()],
			self::TIMESTAMP_ATTRIBUTE => ['nullable', 'integer', 'required_with:' . self::MAC_ATTRIBUTE],
			self::MAC_ATTRIBUTE => ['nullable', 'string', 'required_with:' . self::TIMESTAMP_ATTRIBUTE],
		];
	}

	/**
	 * Merge route parameters and the temporary-link headers into request
	 * data for validation.
	 */
	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge([
			RequestAttribute::PHOTO_ID_ATTRIBUTE => $this->route(RequestAttribute::PHOTO_ID_ATTRIBUTE),
			RequestAttribute::SIZE_VARIANT_TOKEN_ATTRIBUTE => $this->route(RequestAttribute::SIZE_VARIANT_TOKEN_ATTRIBUTE),
			self::TIMESTAMP_ATTRIBUTE => $this->header(self::TIMESTAMP_HEADER),
			self::MAC_ATTRIBUTE => $this->header(self::MAC_HEADER),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string $photo_id */
		$photo_id = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE];
		/** @var string $size_variant_token */
		$size_variant_token = $values[RequestAttribute::SIZE_VARIANT_TOKEN_ATTRIBUTE];

		$this->photo = Photo::query()->with(['albums'])->findOrFail($photo_id);
		/** @var SizeVariantType $size_variant_type the token already passed rules()'s SizeVariantTypeNameRule */
		$size_variant_type = SizeVariantTypeNameRule::resolve($size_variant_token);
		$this->size_variant_type = $size_variant_type;

		$this->size_variant = SizeVariant::query()
			->where('photo_id', '=', $this->photo->id)
			->where('type', '=', $this->size_variant_type)
			->firstOrFail();

		/** @var int|null $timestamp */
		$timestamp = $values[self::TIMESTAMP_ATTRIBUTE] ?? null;
		/** @var string|null $mac */
		$mac = $values[self::MAC_ATTRIBUTE] ?? null;
		$this->timestamp = $timestamp;
		$this->mac = $mac;
	}

	/**
	 * Determines whether `$user` must additionally present a valid
	 * temporary-link signature to be authorized (ADR-0008).
	 *
	 * Guests are only ever authorized via a valid temporary link (FR-056-05)
	 * — always `true`, regardless of config; {@link self::hasValidSignature()}
	 * separately rejects them outright when the feature is globally
	 * disabled. For authenticated users, this mirrors
	 * {@link \App\Services\UrlGenerator::shouldNotUseSignedUrl()}'s
	 * generation-time predicate, re-purposed for validation.
	 */
	private function signatureRequired(?User $user, ConfigManager $config_manager): bool
	{
		if ($user === null) {
			return true;
		}

		if (!$config_manager->getValueAsBool('temporary_image_link_enabled')) {
			return false;
		}

		if ($user->may_administrate) {
			return $config_manager->getValueAsBool('temporary_image_link_when_admin');
		}

		return $config_manager->getValueAsBool('temporary_image_link_when_logged_in');
	}

	/**
	 * Validates the temporary-link signature: the feature must be globally
	 * enabled, both headers must be present (already guaranteed by
	 * rules()'s both-or-neither validation, but a missing pair is still a
	 * failure here, not a pass), the MAC must verify, and the timestamp must
	 * be neither expired nor in the future (FR-056-04).
	 */
	private function hasValidSignature(ConfigManager $config_manager): bool
	{
		if (!$config_manager->getValueAsBool('temporary_image_link_enabled')) {
			return false;
		}

		if ($this->timestamp === null || $this->mac === null) {
			return false;
		}

		if (!(new TemporaryLinkSigner())->verify($this->timestamp, $this->mac)) {
			return false;
		}

		$now = now()->timestamp;
		if ($this->timestamp > $now) {
			return false;
		}

		$life_in_seconds = $config_manager->getValueAsInt('temporary_image_link_life_in_seconds');

		return ($now - $this->timestamp) <= $life_in_seconds;
	}
}
