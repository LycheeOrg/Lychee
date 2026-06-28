<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Enum\OrderSortingType;
use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

/**
 * Authorization and validation request for the Face Maintenance index endpoint.
 *
 * Admin-only: requires the CAN_EDIT settings policy gate.
 */
class FaceMaintenanceIndexRequest extends BaseApiRequest
{
	/** @var 'confidence'|'laplacian_variance' */
	public string $sort_by = 'confidence';

	public OrderSortingType $sort_dir = OrderSortingType::ASC;

	public bool $dismissed_only = false;

	public bool $unassigned_only = false;

	/** @var int<1, 200> */
	public int $per_page = 50;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			'sort_by' => ['nullable', 'string', 'in:confidence,laplacian_variance'],
			'sort_dir' => ['nullable', 'string', new Enum(OrderSortingType::class)],
			'dismissed_only' => ['nullable'],
			'unassigned_only' => ['nullable'],
			'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->sort_by = $values['sort_by'] ?? 'confidence';
		$this->sort_dir = OrderSortingType::tryFrom($values['sort_dir'] ?? 'ASC');
		$this->dismissed_only = self::toBoolean($values['dismissed_only'] ?? false);
		$this->unassigned_only = self::toBoolean($values['unassigned_only'] ?? false);
		$this->per_page = (int) ($values['per_page'] ?? 50);
	}
}
