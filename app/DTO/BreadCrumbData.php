<?php

namespace App\DTO;

class BreadCrumbData
{
	public string $title;
	public ?string $parent_id;
	public ?BreadCrumbData $parent;

	/**
	 * Returns the path associated with the bread crumb data.
	 *
	 * @return string path of albums separated by '/'
	 */
	public function getPath(): string
	{
		$titleArray = [$this->title];
		$parentNode = $this->parent;
		while ($parentNode !== null) {
			array_unshift($titleArray, $parentNode->title);
			$parentNode = $parentNode->parent;
		}

		return implode('/', $titleArray);
	}
}
