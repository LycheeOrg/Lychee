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
	 * Get the list of changelogs from github release docs.
	 *
	 * @return ChangeLogInfo[]
	 *
	 * @throws PcreException
	 */
	public function changeLogs(): array
	{
		$response = (new ChangeLogsRequest())->get_data();
		if ($response === null || $response === '') {
			return [];
		}

		// remove the </style> block at the beginning of the changelogs
		$pos = strpos($response, '</style>');
		if ($pos === false) {
			return [];
		}
		$changelog = substr($response, $pos + strlen('</style>'));

		$major_versions = preg_split('/\\s## .*/', $changelog);
		$major_versions = array_map(fn ($mv) => $this->map_major_version($mv), $major_versions);
		$major_versions = array_filter($major_versions, fn ($mv) => $mv !== []);
		$major_versions = array_values($major_versions);
		$versions = array_merge([], ...$major_versions);

		return $versions;
	}

	/**
	 * Given a major version, returns an array of minor versions.
	 *
	 * @param string $major_version
	 *
	 * @return string[]
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
	 */
	private function map_minor_version_to_changelog(string $minor_version): ChangeLogInfo
	{
		/** @var array{0:string,1:string,2:string,3:string} $data */
		$data = explode("\n", $minor_version, 4);
		$prep_changes = trim($this->replace_links($data[3]));

		return new ChangeLogInfo(
			version: str_replace('v', '', $data[0]),
			date: $data[2],
			changes: $this->converter->convert($prep_changes)->getContent());
	}

	/**
	 * Replace github issue/pulls ids in the changelogs with markdown links.
	 */
	private function replace_links(string $text): string
	{
		$pattern = '/#([0-9]+)/';
		$replacement = '[#$1](https://github.com/LycheeOrg/Lychee/pull/$1)';

		return preg_replace($pattern, $replacement, $text);
	}
}
