<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_GALLERY = 'Gallery';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,details?:string,order?:int,not_on_docker?:bool,is_expert?:bool,level?:int}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'sub_albums_per_page',
				'value' => '30',
				'cat' => self::MOD_GALLERY,
				'type_range' => 'int',
				'description' => 'Number of sub-albums per page.',
				'details' => 'Number of child albums to display per page in paginated album views.',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 51,
				'not_on_docker' => false,
				'level' => 0,
			],
			[
				'key' => 'photos_per_page',
				'value' => '100',
				'cat' => self::MOD_GALLERY,
				'type_range' => 'int',
				'description' => 'Number of photos per page.',
				'details' => 'Number of photos to display per page in paginated album views.',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 52,
				'not_on_docker' => false,
				'level' => 0,
			],
			[
				'key' => 'search_results_per_page',
				'value' => '50',
				'cat' => self::MOD_GALLERY,
				'type_range' => 'int',
				'description' => 'Number of search results per page.',
				'details' => 'Number of items to display per page in search results.',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 53,
				'not_on_docker' => false,
				'level' => 0,
			],
			[
				'key' => 'photos_pagination_ui_mode',
				'value' => 'infinite_scroll',
				'cat' => self::MOD_GALLERY,
				'type_range' => 'infinite_scroll|load_more_button|page_navigation',
				'description' => 'Photo pagination UI mode.',
				'details' => 'Controls how photo pagination is displayed: infinite_scroll (auto-load on scroll), load_more_button (manual "Load More" button), or page_navigation (page numbers with prev/next).',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 54,
				'not_on_docker' => false,
				'level' => 0,
			],
			[
				'key' => 'albums_pagination_ui_mode',
				'value' => 'infinite_scroll',
				'cat' => self::MOD_GALLERY,
				'type_range' => 'infinite_scroll|load_more_button|page_navigation',
				'description' => 'Album pagination UI mode.',
				'details' => 'Controls how album pagination is displayed: infinite_scroll (auto-load on scroll), load_more_button (manual "Load More" button), or page_navigation (page numbers with prev/next).',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 55,
				'not_on_docker' => false,
				'level' => 0,
			],
		];
	}
};
