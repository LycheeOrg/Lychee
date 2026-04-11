<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Table of access permissions.
 *
 * | Operation          | public              | private             | privacy-preserving        | restricted                |
 * |--------------------|---------------------|---------------------|---------------------------|---------------------------|
 * | View People page   | guest               | logged users        | photo/album owner + admin | admin only                |
 * | View face overlays | album access        | logged users        | photo/album owner + admin | photo/album owner + admin |
 * | Create/edit Person | logged users        | logged users        | photo/album owner + admin | admin only                |
 * | Assign face        | logged users        | logged users        | photo/album owner + admin | admin only                |
 * | Trigger scan       | logged users        | logged users        | photo/album owner + admin | photo/album owner + admin |
 * | Claim person       | logged users        | logged users        | logged users              | logged users              |
 * | Merge persons      | logged users        | logged users        | photo/album owner + admin | admin only                |
 * | Dismiss face       | photo owner + admin | photo owner + admin | photo owner + admin       | photo owner + admin       |
 * | Batch face ops     | logged users        | logged users        | photo/album owner + admin | admin only                |
 * | View album people  | album access        | logged users        | photo/album owner + admin | photo/album owner + admin |
 */

enum FacePermissionMode: string
{
	case PUBLIC = 'public';
	case PRIVATE = 'private';
	case PRIVACY_PRESERVING = 'privacy-preserving';
	case RESTRICTED = 'restricted';
}
