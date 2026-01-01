<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CONFIG = 'Admin';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'import_via_url_forbidden_localhost',
				'value' => '1',
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Import via URL must not use localhost',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> Disabling this expose your localhost to the Import via URL functionality and lead to Server-Side Request Forgery (SSRF).',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 20,
			],
			[
				'key' => 'import_via_url_forbidden_local_ip',
				'value' => '1',
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Import via URL must not use local IPs',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> Disabling this expose your internal network to the Import via URL functionality and lead to Server-Side Request Forgery (SSRF).',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 21,
			],
			[
				'key' => 'import_via_url_require_https',
				'value' => '1',
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Import via URL must use https',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> Disabling this will lower the security of the Import via URL functionality.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 22,
			],
			[
				'key' => 'import_via_url_forbidden_ports',
				'value' => '1',
				'cat' => self::CONFIG,
				'type_range' => self::BOOL,
				'description' => 'Import via URL must use port 80 or 443',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> Disabling this will allow the Import via URL to use any ports which may lead to Server-Side Request Forgery (SSRF).',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 23,
			],
		];
	}
};