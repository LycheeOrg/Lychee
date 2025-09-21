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
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,order?:int,not_on_docker?:bool,is_expert?:bool}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'webshop_enabled',
				'value' => '0',
				'cat' => self::MOD_PRO,
				'type_range' => self::BOOL,
				'description' => 'Enable webshop',
				'details' => 'Albums and photos can be set as purchasable items.',
				'is_secret' => true,
				'order' => 10,
				'not_on_docker' => false,
				'level' => 1, // Only for SE.
			],
			[
				'key' => 'webshop_currency',
				'value' => 'EUR',
				'cat' => self::MOD_PRO,
				'type_range' => 'currency', // Ensure that the value is a valid ISO 4217 currency code.
				'description' => 'Purchasable currency',
				'details' => 'The currency in which the prices are displayed and charged. Must be a valid ISO 4217 currency code.',
				'is_secret' => false,
				'order' => 11,
				'not_on_docker' => false,
				'level' => 1, // Only for SE.
			],
			[
				'key' => 'webshop_default_description',
				'value' => 'No description provided.',
				'cat' => self::MOD_PRO,
				'type_range' => self::STRING,
				'description' => 'Default description for purchasable items',
				'details' => 'This description is used when no other description is provided for an album or photo.',
				'is_secret' => false,
				'order' => 12,
				'not_on_docker' => false,
				'level' => 1, // Only for SE.
			],
		];
	}
};
