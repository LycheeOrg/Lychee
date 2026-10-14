<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Enum\FacePhotoAssetType;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\ResolvesSizeVariantAssetTrait;
use App\Models\Face;
use App\Models\SizeVariant;
use App\Models\User;
use App\Rules\RandomIDRule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

/**
 * Request for `GET /api/v3/Faces/{face_id}/photo/{variant}` (Feature 093,
 * API-093-05, ADR-093-01).
 *
 * Authorised for the photo owner or an admin (the quick naming queue rule,
 * FR-093-02), with the same temporary-link signature requirement as
 * `GET /api/v3/Asset/...`. The face may be in any state (named, dismissed):
 * the naming session revisits handled faces.
 */
class GetFacePhotoAssetRequest extends BaseApiRequest
{
	use ResolvesSizeVariantAssetTrait;

	public const FACE_ID_ATTRIBUTE = 'face_id';
	public const VARIANT_ATTRIBUTE = 'variant';

	private string $photo_id;
	private SizeVariant $size_variant;

	public function sizeVariant(): SizeVariant
	{
		return $this->size_variant;
	}

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		/** @var User|null $user */
		$user = Auth::user();

		if ($this->signatureRequired($user) && !$this->isSignatureValid()) {
			$this->signature_check_failed = true;

			return false;
		}

		return config('features.struct-of-array') === true &&
			$user !== null &&
			$this->configs()->getValueAsBool('ai_vision_enabled') &&
			$this->configs()->getValueAsBool('ai_vision_face_enabled') &&
			($user->may_administrate || $this->isPhotoOwner($user));
	}

	private function isPhotoOwner(User $user): bool
	{
		return DB::table('photos')
			->where('id', '=', $this->photo_id)
			->where('owner_id', '=', $user->id)
			->exists();
	}

	/**
	 * {@inheritDoc}
	 *
	 * Missing or invalid signature → 401; signed or session caller without
	 * the right → 401 for a guest, 403 otherwise.
	 */
	protected function failedAuthorization(): void
	{
		throw $this->signature_check_failed || !Auth::check() ? new UnauthenticatedException() : new UnauthorizedException();
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			self::FACE_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			self::VARIANT_ATTRIBUTE => ['required', 'string', new Enum(FacePhotoAssetType::class)],
			self::MAC_ATTRIBUTE => ['nullable', 'string'],
		];
	}

	/**
	 * Merge route parameters and the temporary-link header into request data.
	 */
	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge([
			self::FACE_ID_ATTRIBUTE => $this->route(self::FACE_ID_ATTRIBUTE),
			self::VARIANT_ATTRIBUTE => $this->route(self::VARIANT_ATTRIBUTE),
			self::MAC_ATTRIBUTE => $this->header(self::MAC_HEADER),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string $face_id */
		$face_id = $values[self::FACE_ID_ATTRIBUTE];
		$photo_id = Face::query()->whereKey($face_id)->value('photo_id');
		if ($photo_id === null) {
			throw (new ModelNotFoundException())->setModel(Face::class);
		}
		$this->photo_id = $photo_id;

		$variant = FacePhotoAssetType::from($values[self::VARIANT_ATTRIBUTE]);
		$this->size_variant = $this->resolveFirstSizeVariant($variant);
		$this->mac = $values[self::MAC_ATTRIBUTE] ?? null;
	}

	/**
	 * First existing size variant row among the candidates of `$variant`;
	 * the shared resolver then handles the thumbnail-class fallbacks.
	 */
	private function resolveFirstSizeVariant(FacePhotoAssetType $variant): SizeVariant
	{
		$candidates = $variant->candidates();
		$last = array_pop($candidates);
		foreach ($candidates as $type) {
			$exists = SizeVariant::query()->where('photo_id', '=', $this->photo_id)->where('type', '=', $type)->exists();
			if ($exists) {
				return $this->resolveSizeVariant($this->photo_id, $type);
			}
		}

		return $this->resolveSizeVariant($this->photo_id, $last);
	}
}
