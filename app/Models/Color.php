<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Color.
 *
 * @property int|null $r
 * @property int|null $g
 * @property int|null $b
 */
class Color extends Model
{
	use HasFactory;
	protected $guarded = ['id'];

	public function photo(): BelongsTo
	{
		return $this->belongsTo(Photo::class, 'photo_id', 'id');
	}

	public function is_main($query)
	{
		return $query->where('is_main', '=', 1);
	}
}
