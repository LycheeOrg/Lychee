<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Renamer;

use App\Contracts\Http\Requests\HasDescription;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\RenamerModeType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Rules\DescriptionRule;
use App\Rules\StringRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

/**
 * Request for creating a new renamer rule.
 */
class CreateRenamerRuleRequest extends BaseApiRequest implements HasDescription
{
	use HasDescriptionTrait;

	public string $rule;
	public string $needle;
	public string $replacement;
	public RenamerModeType $mode;
	public int $order;
	public bool $is_enabled;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		$user = Auth::user();

		return $user !== null && ($user->may_administrate || $user->may_upload);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::RULE_ATTRIBUTE => ['required', new StringRule(false, 100)],
			RequestAttribute::DESCRIPTION_ATTRIBUTE => ['present', 'nullable', new DescriptionRule()],
			RequestAttribute::NEEDLE_ATTRIBUTE => ['present', new StringRule(false, 255)],
			RequestAttribute::REPLACEMENT_ATTRIBUTE => ['present', new StringRule(false, 255)],
			RequestAttribute::MODE_ATTRIBUTE => ['required', new Enum(RenamerModeType::class)],
			RequestAttribute::ORDER_ATTRIBUTE => ['required', 'integer', 'min:1'],
			RequestAttribute::IS_ENABLED_ATTRIBUTE => ['required', 'boolean'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->rule = $values[RequestAttribute::RULE_ATTRIBUTE];
		$this->description = $values[RequestAttribute::DESCRIPTION_ATTRIBUTE] ?? '';
		$this->needle = $values[RequestAttribute::NEEDLE_ATTRIBUTE] ?? '';
		$this->replacement = $values[RequestAttribute::REPLACEMENT_ATTRIBUTE] ?? '';
		$this->mode = RenamerModeType::from($values[RequestAttribute::MODE_ATTRIBUTE]);
		$this->order = $values[RequestAttribute::ORDER_ATTRIBUTE];
		$this->is_enabled = self::toBoolean($values[RequestAttribute::IS_ENABLED_ATTRIBUTE]);
	}
}
