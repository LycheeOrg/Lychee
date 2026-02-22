<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

class RequestAttribute
{
	public const ID_ATTRIBUTE = 'id';

	public const USER_ID_ATTRIBUTE = 'user_id';
	public const USER_IDS_ATTRIBUTE = 'user_ids';
	public const USER_GROUP_IDS_ATTRIBUTE = 'group_ids';

	public const GROUP_ID = 'group_id';
	public const NAME_ATTRIBUTE = 'name';
	public const ROLE_ATTRIBUTE = 'role';

	public const EMAIL_ATTRIBUTE = 'email';
	public const ALIAS_ATTRIBUTE = 'alias';
	public const PROVIDER_ATTRIBUTE = 'provider';
	public const TERM_ATTRIBUTE = 'terms';

	public const PARENT_ID_ATTRIBUTE = 'parent_id';

	public const FROM_ID_ATTRIBUTE = 'from_id';
	public const ALBUM_ID_ATTRIBUTE = 'album_id';
	public const ALBUM_IDS_ATTRIBUTE = 'album_ids';
	public const ALBUM_DECORATION_ATTRIBUTE = 'album_decoration';
	public const ALBUM_DECORATION_ORIENTATION_ATTRIBUTE = 'album_decoration_orientation';

	public const PHOTO_ID_ATTRIBUTE = 'photo_id';
	public const PHOTO_IDS_ATTRIBUTE = 'photo_ids';
	public const HEADER_ID_ATTRIBUTE = 'header_id';

	public const TITLE_ATTRIBUTE = 'title';
	public const DATE_ATTRIBUTE = 'date';
	public const UPLOAD_DATE_ATTRIBUTE = 'upload_date';
	public const TAKEN_DATE_ATTRIBUTE = 'taken_at';
	public const DESCRIPTION_ATTRIBUTE = 'description';
	public const LICENSE_ATTRIBUTE = 'license';
	public const ASPECT_RATIO_ATTRIBUTE = 'aspect_ratio';
	public const ALBUM_ASPECT_RATIO_ATTRIBUTE = 'album_aspect_ratio';

	public const USERNAME_ATTRIBUTE = 'username';

	public const PASSWORD_ATTRIBUTE = 'password';
	public const OLD_PASSWORD_ATTRIBUTE = 'old_password';
	public const HAS_QUOTA_ATTRIBUTE = 'has_quota';
	public const QUOTA_ATTRIBUTE = 'quota_kb';
	public const NOTE_ATTRIBUTE = 'note';

	public const SORTING_COLUMN_ATTRIBUTE = 'sorting_column';
	public const SORTING_ORDER_ATTRIBUTE = 'sorting_order';
	public const PHOTO_SORTING_COLUMN_ATTRIBUTE = 'photo_sorting_column';
	public const PHOTO_SORTING_ORDER_ATTRIBUTE = 'photo_sorting_order';
	public const ALBUM_SORTING_COLUMN_ATTRIBUTE = 'album_sorting_column';
	public const ALBUM_SORTING_ORDER_ATTRIBUTE = 'album_sorting_order';
	public const ALBUM_PHOTO_LAYOUT = 'photo_layout';
	public const ALBUM_TIMELINE_ALBUM = 'album_timeline';
	public const ALBUM_TIMELINE_PHOTO = 'photo_timeline';

	public const PAGE_ATTRIBUTE = 'page';

	public const PERMISSION_ID = 'perm_id';
	public const IS_COMPACT_ATTRIBUTE = 'is_compact';
	public const IS_NSFW_ATTRIBUTE = 'is_nsfw';
	public const IS_PINNED_ATTRIBUTE = 'is_pinned';
	public const IS_PUBLIC_ATTRIBUTE = 'is_public';
	public const IS_LINK_REQUIRED_ATTRIBUTE = 'is_link_required';
	public const GRANTS_DOWNLOAD_ATTRIBUTE = 'grants_download';
	public const GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE = 'grants_full_photo_access';
	public const GRANTS_UPLOAD_ATTRIBUTE = 'grants_upload';
	public const GRANTS_EDIT_ATTRIBUTE = 'grants_edit';
	public const GRANTS_DELETE_ATTRIBUTE = 'grants_delete';

	public const IS_AND_ATTRIBUTE = 'is_and';

	public const ALL_ATTRIBUTE = 'all';

	public const FILE_ATTRIBUTE = 'file';
	public const SHALL_OVERRIDE_ATTRIBUTE = 'shall_override';
	public const IS_HIGHLIGHTED_ATTRIBUTE = 'is_highlighted';
	public const RATING_ATTRIBUTE = 'rating';
	public const DIRECTION_ATTRIBUTE = 'direction';

	public const SINGLE_PATH_ATTRIBUTE = 'path';
	public const SIZE_VARIANT_ATTRIBUTE = 'variant';

	public const TAG_ID = 'tag_id';
	public const TAGS_ATTRIBUTE = 'tags';
	public const MAY_UPLOAD_ATTRIBUTE = 'may_upload';
	public const MAY_EDIT_OWN_SETTINGS_ATTRIBUTE = 'may_edit_own_settings';
	public const MAY_ADMINISTRATE = 'may_administrate';
	public const SHARED_ALBUMS_VISIBILITY_ATTRIBUTE = 'shared_albums_visibility';

	/**
	 * Import from server attributes.
	 */
	public const PATH_ATTRIBUTE = 'paths';
	public const DELETE_IMPORTED_ATTRIBUTE = 'delete_imported';
	public const SKIP_DUPLICATES_ATTRIBUTE = 'skip_duplicates';
	public const IMPORT_VIA_SYMLINK_ATTRIBUTE = 'import_via_symlink';
	public const RESYNC_METADATA_ATTRIBUTE = 'resync_metadata';

	public const FILE_LAST_MODIFIED_TIME = 'file_last_modified_time';

	public const COPYRIGHT_ATTRIBUTE = 'copyright';
	public const URLS_ATTRIBUTE = 'urls';

	public const CONFIGS_ATTRIBUTE = 'configs';
	public const CONFIGS_ARRAY_KEY_ATTRIBUTE = 'configs.*.key';
	public const CONFIGS_ARRAY_VALUE_ATTRIBUTE = 'configs.*.value';
	public const CONFIGS_KEY_ATTRIBUTE = 'key';
	public const CONFIGS_VALUE_ATTRIBUTE = 'value';

	public const RENAMER_RULE_ID_ATTRIBUTE = 'rule_id';
	public const RULE_ATTRIBUTE = 'rule';
	public const NEEDLE_ATTRIBUTE = 'needle';
	public const REPLACEMENT_ATTRIBUTE = 'replacement';
	public const MODE_ATTRIBUTE = 'mode';
	public const ORDER_ATTRIBUTE = 'order';
	public const IS_ENABLED_ATTRIBUTE = 'is_enabled';
	public const IS_PHOTO_RULE_ATTRIBUTE = 'is_photo_rule';
	public const IS_ALBUM_RULE_ATTRIBUTE = 'is_album_rule';

	/**
	 * Shop management attributes.
	 */
	public const BASKET_ID_ATTRIBUTE = 'basket_id';
	public const TRANSACTION_ID_ATTRIBUTE = 'transaction_id';
	public const PRICES_ATTRIBUTE = 'prices';
	public const SIZE_VARIANT_TYPE_ATTRIBUTE = 'size_variant_type';
	public const LICENSE_TYPE_ATTRIBUTE = 'license_type';
	public const IS_ACTIVE_ATTRIBUTE = 'is_active';
	public const APPLIES_TO_SUBALBUMS_ATTRIBUTE = 'applies_to_subalbums';
	public const PURCHASABLE_ID_ATTRIBUTE = 'purchasable_id';
	public const PURCHASABLE_IDS_ATTRIBUTE = 'purchasable_ids';
}