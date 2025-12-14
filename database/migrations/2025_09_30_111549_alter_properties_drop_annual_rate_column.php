<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('properties', 'annual_rate')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropColumn('annual_rate');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('properties', 'annual_rate')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->decimal('annual_rate', 12, 2)->default(0);
            });
        }
    }
};
