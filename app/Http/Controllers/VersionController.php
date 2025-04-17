<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Http\Resources\Diagnostics\ChangeLogInfo;
use App\Http\Resources\Root\VersionResource;
use App\Metadata\Json\ChangeLogsRequest;
use Illuminate\Routing\Controller;
use League\CommonMark\CommonMarkConverter;
use Safe\Exceptions\PcreException;
use function Safe\preg_replace;
use function Safe\preg_split;

class VersionController extends Controller
{
	private CommonMarkConverter $converter;

	public function __construct()
	{
		$this->converter = new CommonMarkConverter();
	}

	/**
	 * Retrieve the data about updates (so that it is not fully blocking).
	 *
	 * @return VersionResource
	 */
	public function get(): VersionResource
	{
		return new VersionResource();
	}

	/**
	 * Get the list of changelogs from our github release docs.
	 *
	 * Our release doc follow the following format :
	 *
	 * ```
	 * <style>
	 * ...
	 * </style>
	 *
	 * ## Major version number (Version 6)
	 *
	 * ### Minor version number (v6.4.3)
	 *
	 * Release date
	 *
	 * #### Change informations
	 * - Change 1
	 * - Change 2
	 * ```
	 *
	 * We split first by Major, this gives us an array of chunk for each major version:
	 * - all the Version 6 releases,
	 * - then all the version 5 releases etc...
	 *
	 * We then split the major version chunk by minor version,
	 * this gives us an array of chunk for each minor version.
	 * This is where we need to do a limited explode on `/n`.
	 * The first line (index 0) is the Version number,
	 * The second line (index 1) is skipped (empty),
	 * The thrid line (index 2) is the release date,
	 * The fourth line (index 3) is the changes applied in Markdown.
	 *
	 * We then map the minor version chunk to a ChangeLogInfo object.
	 * Then we flatten the array of major versions to a single array of minor versions
	 * and send that back to the client.
	 *
	 * @return ChangeLogInfo[] list of change logs for each minor version chronologically descending ordered
	 *
	 * @throws PcreException this should not happen
	 */
	public function changeLogs(): array
	{
		$response = (new ChangeLogsRequest())->get_data();
		if ($response === null || $response === '') {
			return [];
		}

		// @codeCoverageIgnoreStart
		return $this->convert($response);
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Convert the changelogs from the response to an array of ChangeLogInfo.
	 * We separate the logic in order to properly test the expected behavior.
	 */
	protected function convert(string $response): array
	{
		// remove the </style> block at the beginning of the changelogs
		$pos = strpos($response, '</style>');
		if ($pos === false) {
			return [];
		}
		$changelog = substr($response, $pos + strlen('</style>'));

		// Apply the split and map functions
		// to get the changelogs in the right format
		$major_versions = $this->split_major_version($changelog);

		// flatten the array of major versions
		$versions = array_merge([], ...$major_versions);

		return $versions;
	}

	/**
	 * Given a changelog, returns an array of arrays of ChangeLogsInfo.
	 * This needs to be flatten.
	 *
	 * @param string $changelog
	 *
	 * @return array<int,ChangeLogInfo[]> list of major version changelogs
	 *
	 * @throws PcreException this should not happen
	 */
	private function split_major_version(string $changelog): array
	{
		$major_versions = preg_split('/\\s## .*/', $changelog);
		$major_versions = array_map(fn ($mv) => $this->map_major_version($mv), $major_versions);
		$major_versions = array_filter($major_versions, fn ($mv) => $mv !== []);

		return array_values($major_versions);
	}

	/**
	 * Given a major version chunk, returns an array of minor versions.
	 *
	 * @param string $major_version a string containing multiple minor versions
	 *
	 * @return ChangeLogInfo[] list of minor version changelogs
	 *
	 * @throws PcreException this should not happen
	 */
	private function map_major_version(string $major_version): array
	{
		$minor_versions = preg_split('/\\s### /', $major_version);
		$minor_version = array_map(fn ($mv) => trim($mv), $minor_versions);
		$minor_version = array_filter($minor_version, fn ($mv) => $mv !== '');
		$minor_version = array_map(fn ($mv) => $this->map_minor_version_to_changelog($mv), $minor_version);

		return array_values($minor_version);
	}

	/**
	 * Given a minor version string, returns a ChangeLogInfo object.
	 * The string is expected to be in the following format:
	 * ```
	 * Minor version number (v6.4.3).
	 *
	 * Release date
	 *
	 * #### Change informations
	 * - Change 1
	 * - Change 2
	 * ```
	 * In order to be properly mapped to the ChangeLogInfo object.
	 */
	private function map_minor_version_to_changelog(string $minor_version): ChangeLogInfo
	{
		/** @var array{0:string,1:string,2:string,3:string} $data */
		$data = explode("\n", $minor_version, 4);
		$prep_changes = trim($this->replace_with_github_links($data[3]));

		return new ChangeLogInfo(
			version: str_replace('v', '', $data[0]),
			date: $data[2],
			changes: $this->converter->convert($prep_changes)->getContent());
	}

	/**
	 * Replace github issue/pulls ids in the changelogs with markdown links.
	 */
	private function replace_with_github_links(string $text): string
	{
		$pattern = '/#([0-9]+)/';
		$replacement = '[#$1](https://github.com/LycheeOrg/Lychee/pull/$1)';

		return preg_replace($pattern, $replacement, $text);
	}
}
