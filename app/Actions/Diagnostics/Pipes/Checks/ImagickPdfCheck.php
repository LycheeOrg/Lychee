<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Models\Configs;
use Illuminate\Support\Facades\Schema;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\PcreException;
use function Safe\file_get_contents;
use function Safe\preg_match;

/**
 * Verify that if Imagick is installed, it is allowed to work with pdf files.
 */
class ImagickPdfCheck implements DiagnosticPipe
{
	const DETAILS = [
		'Make sure to have the policy.xml file in `/etc/ImageMagick-6/policy.xml`.',
		'Verify that the file contains the line <policy domain="coder" rights="read|write" pattern="PDF"/> .',
	];

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!Schema::hasTable('configs')) {
			// @codeCoverageIgnoreStart
			return $next($data);
			// @codeCoverageIgnoreEnd
		}

		if (!Configs::hasImagick()) {
			$data[] = DiagnosticData::info('Imagick is not enabled. Thumbs will not be created for pdf files.', self::class);

			return $next($data);
		}


		try {
			if (!file_exists('/etc/ImageMagick-6/policy.xml')) {
				$data[] = DiagnosticData::warn('The policy.xml file does not exist at the expected location: /etc/ImageMagick-6/policy.xml.', self::class, self::DETAILS);
				return $next($data);
			}

			$imagic_policy = file_get_contents('/etc/ImageMagick-6/policy.xml');
			if (1 === preg_match('/<policy domain="coder" rights="none" pattern="PDF"/', $imagic_policy)) {
				$data[] = DiagnosticData::warn('Imagick is not allowed to create thumbs for pdf files.', self::class,
					['Verify that the /etc/ImageMagick-6/policy.xml file contains the line <policy domain="coder" rights="read|write" pattern="PDF"/> .']);
			}
		} catch (FilesystemException) {
			$data[] = DiagnosticData::warn('Could not determine whether Imagick is allowed to work with pdf files.', self::class, self::DETAILS);
		} catch (PcreException) {
			// Just ignore.
		} catch (\Exception $e) {
			$data[] = DiagnosticData::error('An unexpected error occurred while checking Imagick PDF support.', self::class, self::DETAILS);
			// Just ignore all other exceptions.
		}

		return $next($data);
	}
}
