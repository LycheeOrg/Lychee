<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\Facades\Helpers;
use App\Models\Configs;
use function Safe\exec;
use Spatie\ImageOptimizer\Optimizers\Cwebp;
use Spatie\ImageOptimizer\Optimizers\Gifsicle;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Optipng;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Spatie\ImageOptimizer\Optimizers\Svgo;

/**
 * Verify that we have some image optimization available if enabled.
 */
class ImageOptCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
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
			return $next($data);
		}
		// @codeCoverageIgnoreStart

		$binaryPath = config('image-optimizer.binary_path');

		if ($binaryPath !== '' && substr($binaryPath, -1) !== DIRECTORY_SEPARATOR) {
			$binaryPath = $binaryPath . DIRECTORY_SEPARATOR;
		}

		if (Helpers::isExecAvailable()) {
			foreach ($tools as $tool) {
				$path = exec('command -v ' . $binaryPath . $tool->binaryName());
				if ($path === '') {
					$data[] = 'Warning: lossless_optimization set to 1 but ' . $binaryPath . $tool->binaryName() . ' not found!';
				}
			}
		} else {
			$data[] = 'Warning: lossless_optimization set to 1 but exec() is not enabled.';
		}

		return $next($data);
		// @codeCoverageIgnoreEnd
	}
}
