<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties','khatian_no')) {
                $table->string('khatian_no')->nullable()->after('mouza')->index();
            }
            if (!Schema::hasColumn('properties','jl_no')) {
                $table->string('jl_no')->nullable()->after('khatian_no');
            }
        });
    }

    public function down(): void {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties','khatian_no')) {
                $table->dropColumn('khatian_no');
            }
            if (Schema::hasColumn('properties','jl_no')) {
                $table->dropColumn('jl_no');
            }
        });
    }
};
