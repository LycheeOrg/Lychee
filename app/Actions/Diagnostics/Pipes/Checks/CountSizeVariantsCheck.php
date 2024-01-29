<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * This checks the Database integrity.
 * More precisely if there are any size variants with the filesize missing.
 */
class CountSizeVariantsCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!Schema::hasTable('size_variants')) {
			return $next($data);
		}

		$num = DB::table('size_variants')->where('size_variants.filesize', '=', 0)->count();
		if ($num > 0) {
			// @codeCoverageIgnoreStart
			$data[] = sprintf('Info: Found %d small images without filesizes.', $num);
			$data[] = sprintf('     You can use `php artisan lychee:variant_filesize %d` to compute them.', $num);
			// @codeCoverageIgnoreEnd
		}

		return $next($data);
	}
}