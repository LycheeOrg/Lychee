<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Request for `GET /api/v3/Faces/queue` (Feature 093, API-093-03).
 *
 * Gate: `features.struct-of-array`, AI Vision and face recognition enabled,
 * a logged-in user, and the library-wide batch face right
 * (`AlbumPolicy::CAN_BATCH_FACE_OPS` with a null album — admins in every
 * mode, logged-in users in `public`/`private` mode).
 *
 * Optional `cluster_label` (integer ≥ 0) scopes the queue to one cluster.
 */
class GetFaceQueueRequest extends BaseApiRequest
{
	public const CLUSTER_LABEL_ATTRIBUTE = 'cluster_label';

	private ?int $cluster_label = null;

	public function clusterLabel(): ?int
	{
		return $this->cluster_label;
	}

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return config('features.struct-of-array') === true &&
			Auth::check() &&
			$this->configs()->getValueAsBool('ai_vision_enabled') &&
			$this->configs()->getValueAsBool('ai_vision_face_enabled') &&
			Gate::check(AlbumPolicy::CAN_BATCH_FACE_OPS, [AbstractAlbum::class, null]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			self::CLUSTER_LABEL_ATTRIBUTE => ['sometimes', 'integer', 'min:0'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$label = $values[self::CLUSTER_LABEL_ATTRIBUTE] ?? null;
		$this->cluster_label = $label !== null ? (int) $label : null;
	}
}
