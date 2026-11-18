<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Traits;

use App\Models\User;
use function Safe\copy;
use function Safe\exec;
use function Safe\mkdir;
use function Safe\realpath;
use function Safe\symlink;

/**
 * Feature 115, FX-115-01: temporary import tree.
 *
 * ```
 * <base>/
 *   alice/                 import root of the restricted user
 *     a.jpg
 *     2026/b.jpg
 *     Family/c.jpg
 *     link-inside -> alice/2026
 *     link-outside -> outside
 *     file-outside.jpg -> outside/secret.jpg
 *   alice2/d.jpg           prefix trap
 *   outside/secret.jpg
 * ```
 */
trait CreatesImportTree
{
	protected string $import_base;
	protected string $import_root;

	protected function createImportTree(): void
	{
		$tmp = sys_get_temp_dir() . '/lychee-import-' . bin2hex(random_bytes(4));
		mkdir($tmp);
		$this->import_base = realpath($tmp);
		$this->import_root = $this->import_base . '/alice';

		mkdir($this->import_root . '/2026', 0777, true);
		mkdir($this->import_root . '/Family');
		mkdir($this->import_base . '/alice2');
		mkdir($this->import_base . '/outside');

		$this->placeImage($this->import_root . '/a.jpg', 'tests/Samples/night.jpg');
		$this->placeImage($this->import_root . '/2026/b.jpg', 'tests/Samples/train.jpg');
		$this->placeImage($this->import_root . '/Family/c.jpg', 'tests/Samples/mongolia.jpeg');
		$this->placeImage($this->import_base . '/alice2/d.jpg', 'tests/Samples/night.jpg');
		$this->placeImage($this->import_base . '/outside/secret.jpg', 'tests/Samples/train.jpg');

		symlink($this->import_root . '/2026', $this->import_root . '/link-inside');
		symlink($this->import_base . '/outside', $this->import_root . '/link-outside');
		symlink($this->import_base . '/outside/secret.jpg', $this->import_root . '/file-outside.jpg');
	}

	protected function removeImportTree(): void
	{
		exec('rm -rf ' . escapeshellarg($this->import_base));
	}

	protected function placeImage(string $target, string $sample): void
	{
		copy(base_path($sample), $target);
	}

	protected function restrictTo(User $user, string $path): User
	{
		$user->server_path = $path;
		$user->may_upload = true;
		$user->save();

		return $user->fresh();
	}

	protected function grantFullAccess(User $user): User
	{
		$user->server_path = '/';
		$user->save();

		return $user->fresh();
	}
}
