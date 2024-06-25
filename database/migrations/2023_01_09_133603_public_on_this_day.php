<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'public_on_this_day',
				'value' => '0',
				'cat' => 'Smart Albums',
				'type_range' => self::BOOL,
				'confidentiality' => '0',
				'description' => 'Make "On This Day" smart album accessible to anonymous users',
			],
		];
	}
};
