<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	public const NORWEGIAN = 'nb-no';
	public const CHINESE_TRADITIONAL = '繁體中文';
	public const CHINESE_SIMPLIFIED = '简体中文';

	public const NORWEGIAN_CODE = 'no';
	public const CHINESE_TRADITIONAL_CODE = 'zh_TW';
	public const CHINESE_SIMPLIFIED_CODE = 'zh_CN';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::table('configs')
			->where('value','=', self::CHINESE_TRADITIONAL)
			->update(['value' => self::CHINESE_TRADITIONAL_CODE]);

		DB::table('configs')
			->where('value','=', self::CHINESE_SIMPLIFIED)
			->update(['value' => self::CHINESE_SIMPLIFIED_CODE]);

		DB::table('configs')
			->where('value','=',self::NORWEGIAN)
			->update(['value' => self::NORWEGIAN_CODE]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		DB::table('configs')
			->where('value','=', self::CHINESE_TRADITIONAL_CODE)
			->update(['value' => self::CHINESE_TRADITIONAL]);

		DB::table('configs')
			->where('value','=',self::CHINESE_SIMPLIFIED_CODE)
			->update(['value' => self::CHINESE_SIMPLIFIED]);

		DB::table('configs')
			->where('value','=',self::NORWEGIAN_CODE)
			->update(['value' => self::NORWEGIAN]);
    }
};
