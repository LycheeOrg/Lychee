<?php

declare(strict_types=1);

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_NSFW = 'Mod NSFW';
	public const BOOL = '0|1';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'nsfw_banner_blur_backdrop',
				'value' => '0',
				'confidentiality' => '0',
				'cat' => self::MOD_NSFW,
				'type_range' => self::BOOL,
				'description' => 'Blur background instead of dark red opaque.',
			],
		];
	}
};
