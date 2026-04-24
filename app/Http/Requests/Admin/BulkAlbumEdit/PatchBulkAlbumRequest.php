<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Admin\BulkAlbumEdit;

use App\DTO\BulkAlbumPatchData;
use App\Enum\AspectRatioType;
use App\Enum\ColumnSortingAlbumType;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\LicenseType;
use App\Enum\OrderSortingType;
use App\Enum\PhotoLayoutType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use App\Rules\BooleanRequireSupportRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

/**
 * FormRequest for bulk-patching album metadata and visibility fields.
 *
 * Admin-only. Only fields present in the request body are updated;
 * at least one optional field must be provided.
 * grants_upload requires Supporter Edition.
 */
class PatchBulkAlbumRequest extends BaseApiRequest
{
	protected BulkAlbumPatchData $bulk_album_patch_data;

	public function authorize(): bool
	{
		/** @var User|null */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}

	public function rules(): array
	{
		return [
			'album_ids' => ['required', 'array', 'min:1', 'max:1000'],
			'album_ids.*' => ['required', 'string'],
			'description' => ['sometimes', 'nullable', 'string', 'max:1048576'],
			'copyright' => ['sometimes', 'nullable', 'string', 'max:255'],
			'license' => ['sometimes', 'nullable', new Enum(LicenseType::class)],
			'photo_layout' => ['sometimes', 'nullable', new Enum(PhotoLayoutType::class)],
			'photo_sorting_col' => ['sometimes', 'nullable', new Enum(ColumnSortingPhotoType::class)],
			'photo_sorting_order' => ['sometimes', 'nullable', new Enum(OrderSortingType::class), 'required_with:photo_sorting_col'],
			'album_sorting_col' => ['sometimes', 'nullable', new Enum(ColumnSortingAlbumType::class)],
			'album_sorting_order' => ['sometimes', 'nullable', new Enum(OrderSortingType::class), 'required_with:album_sorting_col'],
			'album_thumb_aspect_ratio' => ['sometimes', 'nullable', new Enum(AspectRatioType::class)],
			'album_timeline' => ['sometimes', 'nullable', new Enum(TimelineAlbumGranularity::class)],
			'photo_timeline' => ['sometimes', 'nullable', new Enum(TimelinePhotoGranularity::class)],
			'is_nsfw' => ['sometimes', 'boolean'],
			'is_public' => ['sometimes', 'boolean'],
			'is_link_required' => ['sometimes', 'boolean'],
			'grants_full_photo_access' => ['sometimes', 'boolean'],
			'grants_download' => ['sometimes', 'boolean'],
			'grants_upload' => ['sometimes', 'boolean', new BooleanRequireSupportRule(false, $this->verify())],
		];
	}

	/**
	 * Ensure at least one optional field (besides album_ids) is present.
	 */
	public function after(): array
	{
		return [
			function (\Illuminate\Validation\Validator $validator): void {
				$optional_fields = [
					'description', 'copyright', 'license', 'photo_layout',
					'photo_sorting_col', 'photo_sorting_order',
					'album_sorting_col', 'album_sorting_order',
					'album_thumb_aspect_ratio', 'album_timeline', 'photo_timeline',
					'is_nsfw', 'is_public', 'is_link_required',
					'grants_full_photo_access', 'grants_download', 'grants_upload',
				];

				$has_any = false;
				foreach ($optional_fields as $field) {
					if ($this->has($field)) {
						$has_any = true;
						break;
					}
				}

				if (!$has_any) {
					$validator->errors()->add('album_ids', 'At least one optional field must be provided for update.');
				}
			},
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$optional_fields = [
			'description', 'copyright', 'license', 'photo_layout',
			'photo_sorting_col', 'photo_sorting_order',
			'album_sorting_col', 'album_sorting_order',
			'album_thumb_aspect_ratio', 'album_timeline', 'photo_timeline',
			'is_nsfw', 'is_public', 'is_link_required',
			'grants_full_photo_access', 'grants_download', 'grants_upload',
		];

		$present = [];
		foreach ($optional_fields as $field) {
			if ($this->has($field)) {
				$present[] = $field;
			}
		}

		$this->bulk_album_patch_data = BulkAlbumPatchData::fromValidated($values, $present);
	}

	public function bulkAlbumPatchData(): BulkAlbumPatchData
	{
		return $this->bulk_album_patch_data;
	}
}
