<?php

namespace App\Actions\Diagnostics\Checks;

use App\Contracts\DiagnosticCheckInterface;
use App\Facades\Helpers;
use App\Models\Configs;
use function Safe\exec;
use Spatie\ImageOptimizer\Optimizers\Cwebp;
use Spatie\ImageOptimizer\Optimizers\Gifsicle;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Optipng;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Spatie\ImageOptimizer\Optimizers\Svgo;

class ImageOptCheck implements DiagnosticCheckInterface
{
	public function check(array &$errors): void
	{
		$tools = [];
		$tools[] = new Cwebp();
		$tools[] = new Gifsicle();
		$tools[] = new Jpegoptim();
		$tools[] = new Optipng();
		$tools[] = new Pngquant();
		$tools[] = new Svgo();

		$settings = Configs::get();
		if (!isset($settings['lossless_optimization']) || $settings['lossless_optimization'] !== '1') {
			return;
		}

		$binaryPath = config('image-optimizer.binary_path');

		if ($binaryPath !== '' && substr($binaryPath, -1) !== DIRECTORY_SEPARATOR) {
			$binaryPath = $binaryPath . DIRECTORY_SEPARATOR;
		}

		if (Helpers::isExecAvailable()) {
			foreach ($tools as $tool) {
				$path = exec('command -v ' . $binaryPath . $tool->binaryName());
				if ($path === '') {
					$errors[] = 'Warning: lossless_optimization set to 1 but ' . $binaryPath . $tool->binaryName() . ' not found!';
				}
			}
		} else {
			$errors[] = 'Warning: lossless_optimization set to 1 but exec() is not enabled.';
		}
	}
}
