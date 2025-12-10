<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CONFIG = 'Admin';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,order?:int,not_on_docker?:bool,is_expert?:bool}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'import_via_url_forbidden_redirect',
				'value' => '1',
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Import via URL should not follow redirections',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> Disabling this expose your localhost to the Import via URL functionality and could lead to Server-Side Request Forgery (SSRF).',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 24,
			],
		];
	}
};
