<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Sitemap;

/**
 * Writes the sitemaps.org 0.9 document (Feature 117, FR-117-06, FR-117-07).
 *
 * The gallery root comes first, without `<lastmod>`, then one entry per album
 * in the order given. An album is addressed by its slug when it has one.
 *
 * @phpstan-type SitemapRow array{id:string,slug:string|null,updated_at:string|null,max_taken_at:string|null}
 */
final class SitemapRenderer
{
	public const XMLNS = 'http://www.sitemaps.org/schemas/sitemap/0.9';

	public function __construct(
		private readonly SitemapLastmod $lastmod,
	) {
	}

	/**
	 * @param string                  $root_url  absolute URL of the gallery root
	 * @param \Closure(string):string $album_url absolute URL of an album from its slug or ID
	 * @param list<SitemapRow>        $rows      albums to list, in tree order
	 * @param int                     $now       generation time (unix timestamp)
	 */
	public function render(string $root_url, \Closure $album_url, array $rows, int $now): string
	{
		$entries = [$this->entry($root_url, null)];
		foreach ($rows as $row) {
			$key = $row['slug'] !== null && $row['slug'] !== '' ? $row['slug'] : $row['id'];
			$entries[] = $this->entry($album_url($key), $this->lastmod->compute($row['updated_at'], $row['max_taken_at'], $now));
		}

		return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
			. '<urlset xmlns="' . self::XMLNS . '">' . "\n"
			. implode('', $entries)
			. '</urlset>' . "\n";
	}

	private function entry(string $loc, ?string $lastmod): string
	{
		$lastmod_element = $lastmod === null ? '' : '<lastmod>' . $lastmod . '</lastmod>';

		return '<url><loc>' . $this->escape($loc) . '</loc>' . $lastmod_element . '</url>' . "\n";
	}

	private function escape(string $value): string
	{
		return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	}
}
