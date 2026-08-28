<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata;

use App\DTO\MetadataWritePayload;
use App\Exceptions\ExternalComponentFailedException;
use App\Image\Files\NativeLocalFile;
use Illuminate\Support\Facades\Process;

/**
 * Embeds title/description/tags/rating into a local image file via exiftool.
 *
 * There is no EXIF-write capability anywhere else in this codebase: the only
 * EXIF library in use, `lychee-org/php-exif`, is read-only. `exiftool` is
 * invoked once per file, with every field's cross-application-compatible
 * EXIF/IPTC/XMP tag triad in a single invocation (see Feature 059 spec's
 * FR-059-08). The command is always built as an array and passed to
 * {@see Process::run()} in that form, never as a concatenated string, so
 * user-controlled title/description/tag text can never reach a shell
 * interpreter (NFR-059-01).
 */
class Writer
{
	/**
	 * Embeds the given payload into the given local file.
	 *
	 * Every field is fully replaced with the payload's current value on every
	 * call (clear-then-set for the list-valued tags) — never a partial diff.
	 * A `null`/empty value clears the corresponding tag(s) rather than being
	 * skipped.
	 *
	 * `exiftool`'s exit code covers the whole invocation, not individual
	 * tags: a rejected/unsupported tag for the file's format is not
	 * separately detectable here (see Feature 059 spec's FR-059-08/Q-059-10)
	 * — only a full non-zero exit is treated as failure.
	 *
	 * @throws ExternalComponentFailedException if `exiftool` exits non-zero
	 */
	public function embed(NativeLocalFile $file, MetadataWritePayload $payload, string $exiftool_path): void
	{
		$result = Process::run($this->buildArguments($file, $payload, $exiftool_path));

		if (!$result->successful()) {
			throw new ExternalComponentFailedException(sprintf('exiftool failed to embed metadata into %s (exit code %d): %s', $file->getRealPath(), $result->exitCode() ?? -1, $result->errorOutput()));
		}
	}

	/**
	 * @return list<string>
	 */
	private function buildArguments(NativeLocalFile $file, MetadataWritePayload $payload, string $exiftool_path): array
	{
		// The `exiftool_path` config is empty by default (it is only set when
		// the user overrides it): an empty value means "resolve via $PATH",
		// the same convention used by ConfigManager::hasExiftool()'s
		// `command -v exiftool` probe and by the php-exif adapter's own
		// lazy default. Unlike those, Symfony's Process needs an actual
		// executable name here, not an empty string.
		$arguments = [
			$exiftool_path !== '' ? $exiftool_path : 'exiftool',
			'-overwrite_original',
			'-P',
			'-charset', 'iptc=UTF8',
			'-charset', 'exif=UTF8',
		];

		$title = $payload->title ?? '';
		$arguments[] = '-XMP-dc:Title=' . $title;
		$arguments[] = '-IPTC:ObjectName=' . $title;
		$arguments[] = '-EXIF:XPTitle=' . $title;

		$description = $payload->description ?? '';
		$arguments[] = '-EXIF:ImageDescription=' . $description;
		$arguments[] = '-IPTC:Caption-Abstract=' . $description;
		$arguments[] = '-XMP-dc:Description=' . $description;

		// Clear existing list tags first, then re-append the current tags,
		// so a removed tag doesn't linger from a previous run.
		$arguments[] = '-IPTC:Keywords=';
		$arguments[] = '-XMP-dc:Subject=';
		foreach ($payload->tags as $tag) {
			$arguments[] = '-IPTC:Keywords+=' . $tag;
			$arguments[] = '-XMP-dc:Subject+=' . $tag;
		}
		$arguments[] = '-EXIF:XPKeywords=' . implode(';', $payload->tags);

		if ($payload->rating !== null) {
			$arguments[] = '-XMP-xmp:Rating=' . (string) $payload->rating;
			$arguments[] = '-EXIF:Rating=' . (string) $payload->rating;
			$arguments[] = '-EXIF:RatingPercent=' . (string) ($payload->rating * 20);
		} else {
			// Empty-value assignment: exiftool treats this as tag deletion.
			$arguments[] = '-XMP-xmp:Rating=';
			$arguments[] = '-EXIF:Rating=';
			$arguments[] = '-EXIF:RatingPercent=';
		}

		$arguments[] = $file->getRealPath();

		return $arguments;
	}
}
