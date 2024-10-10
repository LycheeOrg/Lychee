<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'show_keybinding_help_popup',
				'value' => '1',
				'is_secret' => false,
				'cat' => 'config',
				'type_range' => self::BOOL,
				'description' => 'Display keybinding help pop-up on login.',
			],
			[
				'key' => 'show_keybinding_help_button',
				'value' => '1',
				'is_secret' => false,
				'cat' => 'config',
				'type_range' => self::BOOL,
				'description' => 'Show keybinding help button in header.',
			],
		];
	}
};

