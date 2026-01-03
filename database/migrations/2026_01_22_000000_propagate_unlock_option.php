<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_GALLERY = 'Admin';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,order?:int,not_on_docker?:bool,is_expert?:bool}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'enable_propagate_unlock_option',
				'value' => '0',
				'cat' => self::MOD_GALLERY,
				'type_range' => self::BOOL,
				'description' => 'Enable unlock propagation.',
				'details' => 'When unlocking an album with password, also unlock all albums with that same password.<br><i class="pi pi-exclamation-triangle text-orange-500"></i> This can lead to confidentiality issues if different users share the same album password.',
				'is_secret' => false,
				'is_expert' => true,
				'order' => 25,
				'not_on_docker' => false,
				'level' => 0,
			],
		];
	}
};
