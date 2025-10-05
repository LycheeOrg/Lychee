<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\RenamerModeType;
use App\Models\RenamerRule;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class RenamerRuleResource extends Data
{
	public int $id;
	public int $order;
	public int $owner_id;
	public string $rule;
	public string $description;
	public string $needle;
	public string $replacement;
	public RenamerModeType $mode;
	public bool $is_enabled;
	public bool $is_photo_rule;
	public bool $is_album_rule;

	public function __construct(RenamerRule $renamer_rule)
	{
		$this->id = $renamer_rule->id;
		$this->order = $renamer_rule->order;
		$this->owner_id = $renamer_rule->owner_id;
		$this->rule = $renamer_rule->rule;
		$this->description = $renamer_rule->description;
		$this->needle = $renamer_rule->needle;
		$this->replacement = $renamer_rule->replacement;
		$this->mode = $renamer_rule->mode;
		$this->is_enabled = $renamer_rule->is_enabled;
		$this->is_photo_rule = $renamer_rule->is_photo_rule;
		$this->is_album_rule = $renamer_rule->is_album_rule;
	}

	public static function fromModel(RenamerRule $renamer_rule): RenamerRuleResource
	{
		return new self($renamer_rule);
	}
}
