<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const SQUARE = 'square';
    const JUSTIFIED = 'justified';
    const UNJUSTIFIED = 'unjustified'; //! Legacy
    const MASONRY = 'masonry';
    const GRID = 'grid';
/**
     * Run the migrations.
     */
    public function up(): void
    {
		DB::table('configs')->where('key', '=', 'layout')->update([
				'type_range' => self::SQUARE . '|' . self::JUSTIFIED . '|' . self::MASONRY . '|' . self::GRID ,
				'description' => 'Layout for pictures',
			],
		);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		DB::table('configs')->where('key', '=', 'layout')->update([
            'type_range' => self::SQUARE . '|' . self::JUSTIFIED . '|' . self::UNJUSTIFIED,
            'description' => 'Layout for pictures',
        ],
    );
}
};
