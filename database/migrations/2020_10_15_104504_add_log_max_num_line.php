<?php

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'log_max_num_line',
				'value' => '1000',
				'confidentiality' => '2',
				'cat' => 'Admin',
				'type_range' => self::INT,
				'description' => '',
			],
		];
	}
};
