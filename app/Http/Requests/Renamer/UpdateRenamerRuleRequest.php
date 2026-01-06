<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Renamer;

use App\Contracts\Http\Requests\HasDescription;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\RenamerModeType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Models\RenamerRule;
use App\Rules\DescriptionRule;
use App\Rules\StringRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

/**
 * Request for updating an existing renamer rule.
 */
class UpdateRenamerRuleRequest extends BaseApiRequest implements HasDescription
{
	use HasDescriptionTrait;

	public RenamerRule $renamer_rule;
	public string $rule;
	public string $needle;
	public string $replacement;
	public RenamerModeType $mode;
	public int $order;
	public bool $is_enabled;
	public bool $is_photo_rule;
	public bool $is_album_rule;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		$user = Auth::user();

		if ($user === null || (!$user->may_administrate && !$user->may_upload)) {
			return false;
		}

		// Users can only update their own rules
		return $this->renamer_rule->owner_id === $user->id || $user->may_administrate;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::RENAMER_RULE_ID_ATTRIBUTE => ['required', 'integer'],
			RequestAttribute::RULE_ATTRIBUTE => ['required', new StringRule(false, 100)],
			RequestAttribute::DESCRIPTION_ATTRIBUTE => ['present', 'nullable', new DescriptionRule()],
			RequestAttribute::NEEDLE_ATTRIBUTE => ['present', new StringRule(false, 255)],
			RequestAttribute::REPLACEMENT_ATTRIBUTE => ['present', 'string', 'max:255'],
			RequestAttribute::MODE_ATTRIBUTE => ['required', new Enum(RenamerModeType::class)],
			RequestAttribute::ORDER_ATTRIBUTE => ['required', 'integer', 'min:1'],
			RequestAttribute::IS_ENABLED_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::IS_PHOTO_RULE_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::IS_ALBUM_RULE_ATTRIBUTE => ['required', 'boolean'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->renamer_rule = RenamerRule::findOrFail($values[RequestAttribute::RENAMER_RULE_ID_ATTRIBUTE]);
		$this->rule = $values[RequestAttribute::RULE_ATTRIBUTE];
		$this->description = $values[RequestAttribute::DESCRIPTION_ATTRIBUTE] ?? '';
		$this->needle = $values[RequestAttribute::NEEDLE_ATTRIBUTE];
		$this->replacement = $values[RequestAttribute::REPLACEMENT_ATTRIBUTE];
		$this->mode = RenamerModeType::from($values[RequestAttribute::MODE_ATTRIBUTE]);
		$this->order = $values[RequestAttribute::ORDER_ATTRIBUTE];
		$this->is_enabled = self::toBoolean($values[RequestAttribute::IS_ENABLED_ATTRIBUTE]);
		$this->is_photo_rule = self::toBoolean($values[RequestAttribute::IS_PHOTO_RULE_ATTRIBUTE]);
		$this->is_album_rule = self::toBoolean($values[RequestAttribute::IS_ALBUM_RULE_ATTRIBUTE]);
	}
}
