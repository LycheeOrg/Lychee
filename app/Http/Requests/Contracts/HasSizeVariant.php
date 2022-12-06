<?php

namespace App\Http\Requests\Contracts;

use App\Enum\DonwloadVariantType;

interface HasSizeVariant
{
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

	/**
	 * @return DonwloadVariantType
	 */
	public function sizeVariant(): DonwloadVariantType;
}