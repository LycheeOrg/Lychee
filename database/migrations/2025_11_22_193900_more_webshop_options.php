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
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,details?:string,order?:int,not_on_docker?:bool,is_expert?:bool,level?:int}>
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
				'is_expert' => false,
				'order' => 19,
				'not_on_docker' => false,
				'level' => 1, // Only for SE.
			],
			[
				'key' => 'webshop_lycheeorg_disclaimer_enabled',
				'value' => '1',
				'cat' => self::MOD_PRO,
				'type_range' => self::BOOL,
				'description' => 'Enable LycheeOrg non-liability disclaimer',
				'details' => 'Lychee is provided under MIT license without any warranties. Disabling this option removes the disclaimer from the order page.',
				'is_secret' => true,
				'is_expert' => true,
				'order' => 20,
				'not_on_docker' => false,
				'level' => 1, // Only for SE.
			],
			[
				'key' => 'webshop_auto_fulfill_enabled',
				'value' => '1',
				'cat' => self::MOD_PRO,
				'type_range' => self::BOOL,
				'description' => 'Enable auto-fulfillment of orders.',
				'details' => 'Once a payment is completed, the content is automatically made available to the user when possible.',
				'is_secret' => true,
				'is_expert' => true,
				'order' => 21,
				'not_on_docker' => false,
				'level' => 1, // Only for SE.
			],
			[
				'key' => 'webshop_manual_fulfill_enabled',
				'value' => '1',
				'cat' => self::MOD_PRO,
				'type_range' => self::BOOL,
				'description' => 'Enable auto-fulfillment of orders on manual action.',
				'details' => 'When "Mark as Delivered" is clicked, the content is automatically made available to the user when possible.',
				'is_secret' => true,
				'is_expert' => true,
				'order' => 21,
				'not_on_docker' => false,
				'level' => 1, // Only for SE.
			],
		];
	}
};
