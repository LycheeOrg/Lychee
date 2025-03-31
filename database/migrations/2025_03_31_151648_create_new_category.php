<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('config_categories')->insert([
            [
                'cat' => 'access_rights',
                'name' => 'Access Rights',
                'description' => '',
                'order' => 7,
            ]
        ]);

        DB::table('configs')->where('key', 'grants_download')->update(['cat' => 'access_rights', 'order' => 3]);
        DB::table('configs')->where('key', 'grants_full_photo_access')->update(['cat' => 'access_rights', 'order' => 4]);
        DB::table('configs')->where('key', 'share_button_visible')->update(['cat' => 'access_rights', 'order' => 5]);
        DB::table('configs')->where('key', 'unlock_password_photos_with_url_param')->update(['cat' => 'access_rights', 'order' => 6]);
        DB::table('configs')->where('key', 'login_required')->update(['cat' => 'access_rights', 'order' => 1]);
        DB::table('configs')->where('key', 'login_required_root_only')->update(['cat' => 'access_rights', 'order' => 2]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('configs')->where('key', 'grants_download')->update(['cat' => 'Gallery', 'order' => 20]);
        DB::table('configs')->where('key', 'grants_full_photo_access')->update(['cat' => 'Gallery', 'order' => 21]);
        DB::table('configs')->where('key', 'share_button_visible')->update(['cat' => 'Gallery', 'order' => 22]);
        DB::table('configs')->where('key', 'unlock_password_photos_with_url_param')->update(['cat' => 'Gallery', 'order' => 23]);
        DB::table('configs')->where('key', 'login_required')->update(['cat' => 'Gallery', 'order' => 24]);
        DB::table('configs')->where('key', 'login_required_root_only')->update(['cat' => 'Gallery', 'order' => 25]);

        DB::table('config_categories')->where('cat', 'access_rights')->delete();
    }
};
