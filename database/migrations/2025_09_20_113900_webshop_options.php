<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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
			[
				'key' => 'webshop_allow_guest_checkout',
				'value' => '1',
				'cat' => self::MOD_PRO,
				'type_range' => self::BOOL,
				'description' => 'Allow guest checkout',
				'details' => 'Allow customers to checkout without creating an account.',
				'is_secret' => false,
				'order' => 13,
				'not_on_docker' => false,
				'level' => 1, // Only for SE.
			],
			[
				'key' => 'webshop_terms_url',
				'value' => '',
				'cat' => self::MOD_PRO,
				'type_range' => self::STRING,
				'description' => 'Terms and Conditions URL',
				'details' => 'Optional URL to the Terms and Conditions page.',
				'is_secret' => false,
				'order' => 14,
				'not_on_docker' => false,
				'level' => 1, // Only for SE.
			],
			[
				'key' => 'webshop_privacy_url',
				'value' => '',
				'cat' => self::MOD_PRO,
				'type_range' => self::STRING,
				'description' => 'Privacy Policy URL',
				'details' => 'Optional URL to the Privacy Policy page.',
				'is_secret' => false,
				'order' => 15,
				'not_on_docker' => false,
				'level' => 1, // Only for SE.
			],
			[
				'key' => 'webshop_default_price_cents',
				'value' => '0',
				'cat' => self::MOD_PRO,
				'type_range' => self::INT,
				'description' => 'Default price in cents',
				'details' => 'The default price (in cents) for new purchasable items.',
				'is_secret' => false,
				'order' => 16,
				'not_on_docker' => false,
				'level' => 1, // Only for SE.
			],
			[
				'key' => 'webshop_default_license',
				'value' => 'personal',
				'cat' => self::MOD_PRO,
				'type_range' => 'personal|commercial|extended', // Must match the PurchasableLicenseType enum.
				'description' => 'Default license type',
				'details' => 'The default license type for new purchasable items.',
				'is_secret' => false,
				'order' => 17,
				'not_on_docker' => false,
				'level' => 1, // Only for SE.
			],
			[
				'key' => 'webshop_default_size',
				'value' => 'medium',
				'cat' => self::MOD_PRO,
				'type_range' => 'medium|medium2x|original|full', // Must match the PurchasableSizeVariantType enum.
				'description' => 'Default size variant',
				'details' => 'The default size variant for new purchasable items.',
				'is_secret' => false,
				'order' => 18,
				'not_on_docker' => false,
				'level' => 1, // Only for SE.
			],
		];
	}
};
