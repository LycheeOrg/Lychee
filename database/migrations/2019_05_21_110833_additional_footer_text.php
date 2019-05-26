<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AdditionalFooterText extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasTable('configs')) {
            DB::table('configs')->insert([
			    [
				    'key' => 'additional_footer_text',
				    'value' => '',
				    'confidentiality' => 0,
			    ],
		    ]);
        } else {
            echo "Table configs does not exists\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
            Configs::where('key', '=', 'additional_footer_text')->delete();
        }
    }
}
