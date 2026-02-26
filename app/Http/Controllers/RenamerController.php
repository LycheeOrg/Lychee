<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Actions\Renamer\PreviewAlbums;
use App\Actions\Renamer\PreviewPhotos;
use App\Actions\Renamer\RenameAlbums;
use App\Actions\Renamer\RenamePhotos;
use App\Http\Requests\Renamer\CreateRenamerRuleRequest;
use App\Http\Requests\Renamer\DeleteRenamerRuleRequest;
use App\Http\Requests\Renamer\ListRenamerRulesRequest;
use App\Http\Requests\Renamer\PreviewRenameRequest;
use App\Http\Requests\Renamer\RenameRequest;
use App\Http\Requests\Renamer\TestRenamerRequest;
use App\Http\Requests\Renamer\UpdateRenamerRuleRequest;
use App\Http\Resources\Models\RenamerPreviewResource;
use App\Http\Resources\Models\RenamerRuleResource;
use App\Metadata\Renamer\Renamer;
use App\Models\RenamerRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Controller for managing renamer rules.
 */
class RenamerController extends Controller
{
	private const CHUNK_SIZE = 100;

	/**
	 * Get all renamer rules owned by the authenticated user.
	 *
	 * Admin user can list all rules if they want.
	 *
	 * @param ListRenamerRulesRequest $request
	 *
	 * @return Collection<string|int, RenamerRuleResource>
	 */
	public function index(ListRenamerRulesRequest $request): Collection
	{
		$user = Auth::user();
		$user_id = $user->id;

		$rules = RenamerRule::query()
			->when(!($request->all && $user->may_administrate), function ($query) use ($user_id): void {
				$query->where('owner_id', $user_id);
			})
			->orderBy('order', 'asc')
			->get();

		/** @disregard P1006 */
		return RenamerRuleResource::collect($rules);
	}

	/**
	 * Create a new renamer rule.
	 *
	 * @param CreateRenamerRuleRequest $request
	 *
	 * @return JsonResponse
	 */
	public function store(CreateRenamerRuleRequest $request): JsonResponse
	{
		$user_id = Auth::id();

		$rule_at_order_exists = RenamerRule::query()
			->where('owner_id', Auth::id())
			->where('order', $request->order)
			->exists();

		if ($rule_at_order_exists) {
			// If a rule already exists at the specified order, we need to shift it
			// and all subsequent rules down by 1 to make space for the new rule.
			RenamerRule::query()
				->where('owner_id', $user_id)
				->where('order', '>=', $request->order)
				->increment('order');
		}

		$rule = RenamerRule::create([
			'owner_id' => $user_id,
			'rule' => $request->rule,
			'description' => $request->description(),
			'needle' => $request->needle,
			'replacement' => $request->replacement,
			'mode' => $request->mode,
			'order' => $request->order,
			'is_enabled' => $request->is_enabled,
			'is_photo_rule' => $request->is_photo_rule,
			'is_album_rule' => $request->is_album_rule,
		]);

		return response()->json(RenamerRuleResource::fromModel($rule), Response::HTTP_CREATED);
	}

	/**
	 * Update an existing renamer rule.
	 *
	 * @param UpdateRenamerRuleRequest $request
	 *
	 * @return RenamerRuleResource
	 */
	public function update(UpdateRenamerRuleRequest $request): RenamerRuleResource
	{
		$rule = $request->renamer_rule;
		$new_order = $request->order;
		$old_order = $rule->order;
		$owner_id = $rule->owner_id;

		$rule_at_order_exists = RenamerRule::query()
			->where('owner_id', $owner_id)
			->where('order', $new_order)
			->where('id', '!=', $rule->id)
			->exists();

		if (!$rule_at_order_exists) {
			// order is changing but no conflict, just update the order
			RenamerRule::query()->where('id', $rule->id)
				->update(['order' => $new_order]);
		} elseif ($new_order !== $old_order) {
			// If the order is changing, we need to handle potential conflicts
			// First shift rule to 0.
			RenamerRule::query()->where('id', $rule->id)
				->update(['order' => 0]);

			if ($new_order > $old_order) {
				// Moving up: shift rules between new_order and old_order+1 down by 1
				RenamerRule::query()->where('owner_id', $owner_id)
					->where('order', '>', $old_order)
					->where('order', '<=', $new_order)
					->decrement('order');
			} else {
				// Moving down: shift rules between old_order and new_order up by 1
				RenamerRule::query()->where('owner_id', $owner_id)
					->where('order', '>=', $new_order)
					->where('order', '<', $old_order)
					->increment('order');
			}

			RenamerRule::query()->where('id', $rule->id)
				->update(['order' => $new_order]);
		}

		$rule->refresh();
		$rule->update([
			'rule' => $request->rule,
			'description' => $request->description(),
			'needle' => $request->needle,
			'replacement' => $request->replacement,
			'mode' => $request->mode,
			'is_enabled' => $request->is_enabled,
			'is_photo_rule' => $request->is_photo_rule,
			'is_album_rule' => $request->is_album_rule,
		]);

		return RenamerRuleResource::fromModel($rule);
	}

	/**
	 * Delete a renamer rule.
	 *
	 * @param DeleteRenamerRuleRequest $request
	 *
	 * @return void
	 */
	public function destroy(DeleteRenamerRuleRequest $request): void
	{
		$request->renamer_rule->delete();
	}

	/**
	 * Preview renamer rule application on photos/albums.
	 *
	 * Returns an array of items whose title would change, with original and new titles.
	 *
	 * @param PreviewRenameRequest $request
	 * @param PreviewPhotos        $preview_photos
	 * @param PreviewAlbums        $preview_albums
	 *
	 * @return Collection<int,RenamerPreviewResource>
	 */
	public function preview(PreviewRenameRequest $request, PreviewPhotos $preview_photos, PreviewAlbums $preview_albums): Collection
	{
		$user_id = Auth::id();
		$target = $request->target;
		$scope = $request->scope;
		$rule_ids = $request->rule_ids;

		return match ($target) {
			'photos' => $preview_photos->execute(
				$user_id,
				$rule_ids,
				$request->photoIds(),
				$request->album_id,
				$scope
			),
			default => $preview_albums->execute(
				$user_id,
				$rule_ids,
				$request->albumIds(),
				$request->album_id,
				$scope
			),
		};
	}

	/**
	 * Test renamer rules against a candidate string.
	 *
	 * @param TestRenamerRequest $request
	 *
	 * @return JsonResponse
	 */
	public function test(TestRenamerRequest $request): JsonResponse
	{
		$user_id = Auth::id();
		$candidate = $request->candidate;

		// Create a Renamer instance for the current user
		$renamer = new Renamer($user_id);

		// Apply the renamer rules to the candidate string
		$result = $renamer->handle($candidate);

		return response()->json([
			'original' => $candidate,
			'result' => $result,
		]);
	}

	/**
	 * Rename photos and/or albums based on the user's renamer rules.
	 *
	 * @param RenameRequest $request
	 *
	 * @return void
	 */
	public function rename(RenameRequest $request): void
	{
		$user_id = Auth::id();
		$rule_ids = count($request->rule_ids) > 0 ? $request->rule_ids : null;

		if (count($request->photoIds()) > 0) {
			$action = new RenamePhotos();
			$action->execute($user_id, $request->photoIds(), $rule_ids);
		}

		if (count($request->albumIds()) > 0) {
			$action = new RenameAlbums();
			$action->execute($user_id, $request->albumIds(), $rule_ids);
		}
	}
}
