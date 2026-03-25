<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Webhook\PatchWebhookRequest;
use App\Http\Requests\Webhook\StoreWebhookRequest;
use App\Http\Requests\Webhook\UpdateWebhookRequest;
use App\Http\Resources\Models\WebhookResource;
use App\Models\User;
use App\Models\Webhook;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\PaginatedDataCollection;

/**
 * Admin controller for managing outgoing webhook configurations.
 *
 * All methods require the authenticated user to be an administrator.
 */
class WebhookController extends Controller
{
	/**
	 * List all webhook configurations.
	 *
	 * @return PaginatedDataCollection<(int|string),WebhookResource>
	 */
	public function index(): PaginatedDataCollection
	{
		$this->assertAdmin();

		$webhooks = Webhook::query()->orderBy('created_at', 'asc')->paginate(50);

		return WebhookResource::collect($webhooks, PaginatedDataCollection::class);
	}

	/**
	 * Create a new webhook configuration.
	 */
	public function store(StoreWebhookRequest $request): WebhookResource
	{
		$validated = $request->validated();
		unset($validated['webhook_id']);

		/** @var Webhook $webhook */
		$webhook = Webhook::create($validated);

		return new WebhookResource($webhook);
	}

	/**
	 * Retrieve a single webhook configuration.
	 */
	public function show(string $webhook): WebhookResource
	{
		$this->assertAdmin();

		/** @var Webhook $model */
		$model = Webhook::findOrFail($webhook);

		return new WebhookResource($model);
	}

	/**
	 * Fully replace a webhook configuration.
	 */
	public function update(UpdateWebhookRequest $request): WebhookResource
	{
		$validated = $request->validated();
		unset($validated['webhook_id']);

		$request->webhook->fill($validated)->save();

		return new WebhookResource($request->webhook);
	}

	/**
	 * Partially update a webhook configuration.
	 */
	public function patch(PatchWebhookRequest $request): WebhookResource
	{
		$validated = $request->validated();
		unset($validated['webhook_id']);

		$request->webhook->fill($validated)->save();

		return new WebhookResource($request->webhook);
	}

	/**
	 * Hard-delete a webhook configuration.
	 */
	public function destroy(string $webhook): void
	{
		$this->assertAdmin();

		/** @var Webhook $model */
		$model = Webhook::findOrFail($webhook);
		$model->delete();
	}

	/**
	 * Ensure the authenticated user is an administrator.
	 */
	private function assertAdmin(): void
	{
		/** @var User|null */
		$user = Auth::user();
		if ($user?->may_administrate !== true) {
			abort(403, 'Admin access required.');
		}
	}
}
