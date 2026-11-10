<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Requests\Person\GetPeopleListRequest;
use App\Http\Resources\V3\PeopleListResource;
use App\Models\Face;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * `GET /api/v3/People` (Feature 093, API-093-04): the people list the face
 * quick naming view filters client-side. One `toBase()` SELECT
 * (NFR-093-05); non-admins see the same set as `GET /api/v2/People`
 * (NFR-093-07).
 */
class PeopleListController extends Controller
{
	public function index(GetPeopleListRequest $_request): PeopleListResource
	{
		/** @var User|null $user */
		$user = Auth::user();

		$rows = Person::query()
			->when($user?->may_administrate !== true, fn ($q) => $q->searchable($user?->id))
			->leftJoin('faces', 'faces.id', '=', 'persons.representative_face_id')
			->orderBy('persons.name')
			->orderBy('persons.id')
			->select(['persons.id', 'persons.name', 'persons.face_count', 'faces.crop_token'])
			->selectSub(self::bestCropToken(), 'best_crop_token')
			->toBase()
			->get();

		$ids = [];
		$names = [];
		$face_counts = [];
		$crop_urls = [];
		foreach ($rows as $row) {
			$ids[] = $row->id;
			$names[] = $row->name;
			$face_counts[] = (int) $row->face_count;
			$crop_urls[] = Face::cropUrlFromToken($row->crop_token ?? $row->best_crop_token);
		}

		return new PeopleListResource(ids: $ids, names: $names, face_counts: $face_counts, crop_urls: $crop_urls);
	}

	/**
	 * Crop of the person's highest-confidence non-dismissed face, used when no
	 * representative face is pinned (same fallback as `PersonResource`).
	 */
	private static function bestCropToken(): Builder
	{
		return DB::table('faces as best')
			->select('best.crop_token')
			->whereColumn('best.person_id', 'persons.id')
			->where('best.is_dismissed', '=', false)
			->whereNotNull('best.crop_token')
			->orderByDesc('best.confidence')
			->orderBy('best.id')
			->limit(1);
	}
}
