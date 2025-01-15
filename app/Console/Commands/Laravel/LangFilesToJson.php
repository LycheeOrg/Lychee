<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands\Laravel;

use Illuminate\Console\Command;
use Safe\Exceptions\PcreException;
use function Safe\file_put_contents;
use function Safe\json_encode;
use function Safe\preg_replace;
use function Safe\scandir;

class LangFilesToJson extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lang:json';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Convert Laravel language files from PHP to JSON';

	/**
	 * @param array<string,mixed> $data
	 *
	 * @return array<string,mixed>
	 *
	 * @throws PcreException
	 */
	public function convert(array $data): array
	{
		$result = [];

		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$result[$key] = $this->convert($value);
			} else {
				if (strpos($value, ':') !== false) {
					$value = preg_replace('/:(\w+)/', '{$1}', $value);
				}
				$result[$key] = $value;
			}
		}

		return $result;
	}

	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		$sourceDir = base_path('lang/');
		$targetDir = base_path('lang/');

		$languages = array_diff(scandir($sourceDir), ['.', '..']);

		foreach ($languages as $language) {
			if (!is_dir($sourceDir . $language)) {
				continue;
			}

			$languageDir = $sourceDir . $language . '/';
			/** @var string[] */
			$files = array_diff(scandir($languageDir), ['.', '..']);

			$translations = [];

			foreach ($files as $file) {
				$filePath = $languageDir . $file;
				$translation = require $filePath;

				$translation = $this->convert($translation);

				$translations[str_replace('.php', '', $file)] = $translation;
			}

			$targetPath = $targetDir . $language . '.json';

			file_put_contents($targetPath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
		}

		$this->info('Language files compiled to JSON successfully!');
	}
}