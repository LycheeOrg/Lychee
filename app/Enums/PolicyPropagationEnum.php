<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self refresh()
 * @method static self cut()
 * @method static self force()
 */
final class PolicyPropagationEnum extends Enum
{
	/**
	 * @return array<string, int|string>
	 */
	protected static function values(): array
	{
		return [
			'refresh' => 0,
			'cut' => 1,
			'force' => 2,
		];
	}
}
