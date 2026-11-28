<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Rules;

final class DescriptionRule extends StringRule
{
	public function __construct()
	{
		parent::__construct(true, self::maxLength());
	}

	/**
	 * Maximum length of a description, as counted by {@see StringRule::passes()}.
	 * Also sent to the front end through `InitConfig::$description_max_length`.
	 */
	public static function maxLength(): int
	{
		return intval(config('features.description-max-length', 1000));
	}
}
