<?php

namespace App\Contracts\Http\Requests;

class RequestAttribute
{
	public const ID_ATTRIBUTE = 'id';

	public const USER_ID_ATTRIBUTE = 'user_id';
	public const USER_IDS_ATTRIBUTE = 'user_ids';

	public const EMAIL_ATTRIBUTE = 'email';

	public const PARENT_ID_ATTRIBUTE = 'parent_id';

	public const ALBUM_ID_ATTRIBUTE = 'album_id';
	public const ALBUM_IDS_ATTRIBUTE = 'album_ids';
	public const ALBUM_DECORATION_ATTRIBUTE = 'album_decoration';
	public const ALBUM_DECORATION_ORIENTATION_ATTRIBUTE = 'album_decoration_orientation';

	public const PHOTO_ID_ATTRIBUTE = 'photo_id';
	public const PHOTO_IDS_ATTRIBUTE = 'photo_ids';

	public const TITLE_ATTRIBUTE = 'title';
	public const UPLOAD_DATE_ATTRIBUTE = 'upload_date';
	public const DESCRIPTION_ATTRIBUTE = 'description';
	public const LICENSE_ATTRIBUTE = 'license';
	public const ASPECT_RATIO_ATTRIBUTE = 'aspect_ratio';
	public const ALBUM_ASPECT_RATIO_ATTRIBUTE = 'album_aspect_ratio';

	public const USERNAME_ATTRIBUTE = 'username';

	public const PASSWORD_ATTRIBUTE = 'password';
	public const OLD_PASSWORD_ATTRIBUTE = 'old_password';

	public const SORTING_COLUMN_ATTRIBUTE = 'sorting_column';
	public const SORTING_ORDER_ATTRIBUTE = 'sorting_order';
	public const PHOTO_SORTING_COLUMN_ATTRIBUTE = 'photo_sorting_column';
	public const PHOTO_SORTING_ORDER_ATTRIBUTE = 'photo_sorting_order';
	public const ALBUM_SORTING_COLUMN_ATTRIBUTE = 'album_sorting_column';
	public const ALBUM_SORTING_ORDER_ATTRIBUTE = 'album_sorting_order';

	public const PERMISSION_ID = 'perm_id';
	public const IS_COMPACT_ATTRIBUTE = 'is_compact';
	public const IS_NSFW_ATTRIBUTE = 'is_nsfw';
	public const IS_PUBLIC_ATTRIBUTE = 'is_public';
	public const IS_LINK_REQUIRED_ATTRIBUTE = 'is_link_required';
	public const GRANTS_DOWNLOAD_ATTRIBUTE = 'grants_download';
	public const GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE = 'grants_full_photo_access';
	public const GRANTS_UPLOAD_ATTRIBUTE = 'grants_upload';
	public const GRANTS_EDIT_ATTRIBUTE = 'grants_edit';
	public const GRANTS_DELETE_ATTRIBUTE = 'grants_delete';

	public const FILE_ATTRIBUTE = 'file';
	public const SHALL_OVERRIDE_ATTRIBUTE = 'shall_override';
	public const IS_STARRED_ATTRIBUTE = 'is_starred';
	public const DIRECTION_ATTRIBUTE = 'direction';

	public const SINGLE_PATH_ATTRIBUTE = 'path';

	/**
	 * Due to historic reasons the attribute which stores the type of
	 * size variant is called `kind`.
	 * Note that the designation `kind` is excessively used for various
	 * things with different semantic meanings.
	 * In other contexts, `kind` may also refer to the category of media
	 * object (i.e. `'photo'` versus `'video'`) or the specific MIME type
	 * (i.e. `'image/jpeg'`, `'image/png'`, etc.).
	 *
	 * TODO: Maybe rename the attribute in the back- and front-end to avoid overloading the same term.
	 */
	public const SIZE_VARIANT_ATTRIBUTE = 'kind';

	public const TAGS_ATTRIBUTE = 'tags';
	public const MAY_UPLOAD_ATTRIBUTE = 'may_upload';
	public const MAY_EDIT_OWN_SETTINGS_ATTRIBUTE = 'may_edit_own_settings';

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
}