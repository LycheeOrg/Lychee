<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO;

use App\Models\Album;

/**
 * Represents a node in the folder tree structure for importing.
 */
class FolderNode
{
	public string $name;
	public string $path;
	public ?Album $album = null;
	/** @var array<int,string> List of images in this folder */
	public array $images = [];
	/** @var array<int,FolderNode> List of child folders */
	public array $children = [];
	/** @var ?FolderNode Parent folder node */
	public ?FolderNode $parent = null;

	public function __construct(string $name, string $path, ?FolderNode $parent = null)
	{
		$this->name = $name;
		$this->path = $path;
		$this->parent = $parent;
	}

	/**
	 * Removes empty nodes (no children and no images) recursively.
	 *
	 * @return bool True if this node should be kept, false if it should be removed
	 */
	public function pruneEmptyNodes(): bool
	{
		// First process all children
		foreach ($this->children as $key => $child) {
			if (!$child->pruneEmptyNodes()) {
				unset($this->children[$key]);
			}
		}

		// Re-index the array
		if (count($this->children) > 0) {
			$this->children = array_values($this->children);
		}

		// Node should be kept if it has images or children
		return count($this->images) > 0 || count($this->children) > 0;
	}
}
