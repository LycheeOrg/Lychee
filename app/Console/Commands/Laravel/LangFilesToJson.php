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
		$source_dir = base_path('lang/');
		$target_dir = base_path('lang/');

		$languages = array_diff(scandir($source_dir), ['.', '..']);

		foreach ($languages as $language) {
			if (!is_dir($source_dir . $language)) {
				continue;
			}

			$language_dir = $source_dir . $language . '/';
			/** @var string[] */
			$files = array_diff(scandir($language_dir), ['.', '..']);

			$translations = [];

			foreach ($files as $file) {
				$file_path = $language_dir . $file;
				$translation = require $file_path;

				$translation = $this->convert($translation);

				$translations[str_replace('.php', '', $file)] = $translation;
			}

			$target_path = $target_dir . $language . '.json';

			file_put_contents($target_path, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
		}

		$this->info('Language files compiled to JSON successfully!');
	}
}