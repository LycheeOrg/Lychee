<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Facades\Helpers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use function Safe\exec;

/**
 * Add two new configuration to give the ability to set which path the binaries are found.
 */
return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$path_ffmpeg = '/usr/bin/ffmpeg';
		$path_ffprobe = '/usr/bin/ffprobe';

		if (Helpers::isExecAvailable()) {
			try {
				$cmd_output_ffmpeg = exec('command -v ffmpeg');
				$cmd_output_ffprobe = exec('command -v ffprobe');
			} catch (\Exception $e) {
				$cmd_output_ffmpeg = false;
				$cmd_output_ffprobe = false;
			}
			$path_ffmpeg = $cmd_output_ffmpeg === false ? '/usr/bin/ffmpeg' : $cmd_output_ffmpeg;
			$path_ffprobe = $cmd_output_ffprobe === false ? '/usr/bin/ffprobe' : $cmd_output_ffprobe;
		}

		DB::table('configs')->insert([
			[
				'key' => 'ffmpeg_path',
				'value' => $path_ffmpeg,
				'confidentiality' => 1,
				'cat' => 'Image Processing',
				'type_range' => 'string',
				'description' => 'Path to the binary of ffmpeg',
			],
			[
				'key' => 'ffprobe_path',
				'value' => $path_ffprobe,
				'confidentiality' => 1,
				'cat' => 'Image Processing',
				'type_range' => 'string',
				'description' => 'Path to the binary of ffprobe',
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @throws InvalidArgumentException
	 */
	public function down(): void
	{
		DB::table('configs')
			->where('key', '=', 'ffmpeg_path')
			->orWhere('key', '=', 'ffprobe_path')
			->delete();
	}
};
