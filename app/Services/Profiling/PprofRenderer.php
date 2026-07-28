<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Profiling;

use App\DTO\Profiling\PprofRenderResult;
use Symfony\Component\Process\Exception\ExceptionInterface as SymfonyProcessException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Renders a `.pprof` dump as an SVG call-graph by shelling out to the
 * `pprof`/`google-pprof` CLI (binary name configurable, see FR-053-04),
 * which itself invokes Graphviz's `dot`. Both are bundled in the official
 * Docker image (Q-053-02); non-Docker installs must install them manually
 * per docs/specs/2-how-to/enable-memory-profiler.md.
 */
class PprofRenderer
{
	public function __construct(
		private ExecutableFinder $executable_finder = new ExecutableFinder(),
	) {
	}

	public function binary(): string
	{
		return (string) config('features.memory-profiler-pprof-bin');
	}

	public function isAvailable(): bool
	{
		return $this->executable_finder->find($this->binary()) !== null;
	}

	/**
	 * @param string $pprof_absolute_path absolute path to the `.pprof` dump
	 */
	public function render(string $pprof_absolute_path): PprofRenderResult
	{
		if (!$this->isAvailable()) {
			return PprofRenderResult::binaryMissing($this->binary());
		}

		try {
			$process = new Process([$this->binary(), '-svg', $pprof_absolute_path]);
			$process->setTimeout(30);
			$process->run();

			if (!$process->isSuccessful()) {
				return PprofRenderResult::processError($process->getErrorOutput());
			}

			return PprofRenderResult::success($process->getOutput());
		} catch (SymfonyProcessException $e) {
			return PprofRenderResult::processError($e->getMessage());
		}
	}
}
