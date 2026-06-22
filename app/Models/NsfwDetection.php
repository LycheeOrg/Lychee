<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Enum\NsfwDetectionLabel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int                             $id
 * @property string                          $photo_id
 * @property NsfwDetectionLabel              $label
 * @property float                           $confidence
 * @property int                             $bbox_x
 * @property int                             $bbox_y
 * @property int                             $bbox_width
 * @property int                             $bbox_height
 * @property int|null                        $area_pixels
 * @property float|null                      $area_ratio
 * @property bool                            $is_block
 * @property bool                            $is_review
 * @property bool                            $is_sensitive
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class NsfwDetection extends Model
{
	public const UPDATED_AT = null;

	protected $table = 'nsfw_detections';

	protected $fillable = [
		'photo_id',
		'label',
		'confidence',
		'bbox_x',
		'bbox_y',
		'bbox_width',
		'bbox_height',
		'area_pixels',
		'area_ratio',
		'is_block',
		'is_review',
		'is_sensitive',
	];

	protected $casts = [
		'label' => NsfwDetectionLabel::class,
		'confidence' => 'float',
		'bbox_x' => 'integer',
		'bbox_y' => 'integer',
		'bbox_width' => 'integer',
		'bbox_height' => 'integer',
		'area_pixels' => 'integer',
		'area_ratio' => 'float',
		'is_block' => 'boolean',
		'is_review' => 'boolean',
		'is_sensitive' => 'boolean',
	];

	/**
	 * @return BelongsTo<Photo,$this>
	 */
	public function photo(): BelongsTo
	{
		return $this->belongsTo(Photo::class, 'photo_id', 'id');
	}
}
