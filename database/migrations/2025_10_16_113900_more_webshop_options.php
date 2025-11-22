<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_PRO = 'Mod Pro';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,details?:string,order?:int,not_on_docker?:bool,level?:int}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'webshop_offline',
				'value' => '0',
				'cat' => self::MOD_PRO,
				'type_range' => self::BOOL,
				'description' => 'Keep webshop offline',
				'details' => 'All payment processing will be skipped. Orders will be marked as OFFLINE instead of going through the payment flow.',
				'is_secret' => true,
				'order' => 19,
				'not_on_docker' => false,
				'level' => 1, // Only for SE.
			],
		];
	}
};
