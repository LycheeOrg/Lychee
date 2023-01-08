<?php

namespace App\Contracts\Http\Requests;

class RequestAttribute
{
	public const ID_ATTRIBUTE = 'id';

	public const USER_ID_ATTRIBUTE = 'userID';
	public const USER_IDS_ATTRIBUTE = 'userIDs';

	public const PARENT_ID_ATTRIBUTE = 'parent_id';

	public const ALBUM_ID_ATTRIBUTE = 'albumID';
	public const ALBUM_IDS_ATTRIBUTE = 'albumIDs';
	public const ALBUM_DECORATION_ATTRIBUTE = 'album_decoration';
	public const ALBUM_DECORATION_ORIENTATION_ATTRIBUTE = 'album_decoration_orientation';

	public const PHOTO_ID_ATTRIBUTE = 'photoID';
	public const PHOTO_IDS_ATTRIBUTE = 'photoIDs';

	public const TITLE_ATTRIBUTE = 'title';
	public const DATE_ATTRIBUTE = 'date';
	public const DESCRIPTION_ATTRIBUTE = 'description';
	public const LICENSE_ATTRIBUTE = 'license';

	public const USERNAME_ATTRIBUTE = 'username';

	public const PASSWORD_ATTRIBUTE = 'password';
	public const OLD_PASSWORD_ATTRIBUTE = 'oldPassword';

	public const SORTING_COLUMN_ATTRIBUTE = 'sorting_column';
	public const SORTING_ORDER_ATTRIBUTE = 'sorting_order';

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
}