<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\SmartAlbums;

use App\DTO\AlbumSortingCriterion;
use App\Enum\AspectRatioType;
use App\Enum\LicenseType;
use App\Enum\SmartAlbumType;
use App\Enum\TimelineAlbumGranularity;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UntaggedAlbum extends BaseSmartAlbum
{
    private static ?self $instance = null;
    public const ID = SmartAlbumType::UNTAGGED->value;

    /**
     * These properties must be declared here to be accessible by other classes.
     * They are defined with a default value of null, as they are not applicable to smart albums.
     */
    public ?LicenseType $license = null;
    public ?AlbumSortingCriterion $album_sorting = null;
    public ?AspectRatioType $album_thumb_aspect_ratio = null;
    public ?TimelineAlbumGranularity $album_timeline = null;
    public ?string $header_id = null;
    public ?string $cover_id = null;
    public string $id;
    public string $title;

    /**
     * @throws ConfigurationKeyMissingException
     * @throws FrameworkException
     */
    public function __construct()
    {
        parent::__construct(
            id: SmartAlbumType::UNTAGGED,
            smart_condition: function (Builder $query): void {
                $query->doesntHave('tags');
            }
        );
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * In the case of UNTAGGED, we cannot determine whether the photo is visible or not from its parent.
     * If the UNTAGGED album is made private, then all the pictures in it are visible to owner only.
     *
     * @return \App\Eloquent\FixedQueryBuilder<Photo>
     */
    public function photos(): Builder
    {
        $user_id = Auth::id();
        $query = Photo::where('owner_id', $user_id)
            ->with(['size_variants', 'statistics', 'palette', 'tags'])
            ->where($this->smart_photo_condition);

        return $query;
    }

    public function getPaginatedPhotos($per_page = null, $page = null)
    {
        $photo_per_page = $per_page ?? Configs::getValueAsInt('untagged_photos_pagination_limit');
        $current_page = $page ?? 1;

        return $this->photos()
            ->orderBy('created_at', 'desc')
            ->paginate($photo_per_page, ['*'], 'page', $current_page);
    }
}
