<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\AlbumListingScope;
use App\Http\Requests\BaseApiRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Request shared by root's index/buckets/rights and
 * `/Albums/persons`/`/Albums/pinned` — five methods across two
 * controllers reuse this one class since their `scope` validation is
 * identical: no album to resolve, just the feature flag gate plus the
 * `own`\|`shared` rule.
 *
 * An **authenticated** caller must supply exactly one of `own`\|`shared`
 * (422 otherwise — no implicit default). An **unauthenticated** caller may
 * omit `scope` (defaults to `shared`) or pass `shared` explicitly; passing
 * `own` as a guest is 422 — a guest can never have an "own" set, and
 * silently returning an empty result would hide a client bug.
 */
class GetScopedAlbumsRequest extends BaseApiRequest
{
	private AlbumListingScope $scope;

	public function scope(): AlbumListingScope
	{
		return $this->scope;
	}

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return config('features.struct-of-array') === true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		$allowed = Auth::check() ? ['own', 'shared'] : ['shared'];

		return [
			RequestAttribute::SCOPE_ATTRIBUTE => [
				Auth::check() ? 'required' : 'nullable',
				Rule::in($allowed),
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string|null $scope */
		$scope = $values[RequestAttribute::SCOPE_ATTRIBUTE] ?? null;
		$this->scope = $scope !== null ? AlbumListingScope::from($scope) : AlbumListingScope::SHARED;
	}
}
