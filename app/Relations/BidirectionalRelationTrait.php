<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Relations;

trait BidirectionalRelationTrait
{
	protected string $foreign_method_name;

	public function getForeignMethodName(): string
	{
		return $this->foreign_method_name;
	}
}
