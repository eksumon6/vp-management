<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // নিরাপদভাবে কলাম থাকলে তবেই ড্রপ করা হবে
        if (Schema::hasColumn('properties', 'dag_no') ||
            Schema::hasColumn('properties', 'area_decimal') ||
            Schema::hasColumn('properties', 'land_class') ||
            Schema::hasColumn('properties', 'usage_class')) {
            Schema::table('properties', function (Blueprint $table) {
                if (Schema::hasColumn('properties', 'dag_no')) $table->dropColumn('dag_no');
                if (Schema::hasColumn('properties', 'area_decimal')) $table->dropColumn('area_decimal');
                if (Schema::hasColumn('properties', 'land_class')) $table->dropColumn('land_class');
                if (Schema::hasColumn('properties', 'usage_class')) $table->dropColumn('usage_class');
            });
        }
    }

    public function down(): void
    {
        // প্রয়োজন হলে আগের কলামগুলো ফিরিয়ে আনতে চাইলে এখানে যোগ করতে পারেন
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties','dag_no'))        $table->string('dag_no',100)->nullable();
            if (!Schema::hasColumn('properties','area_decimal'))  $table->decimal('area_decimal',12,4)->nullable();
            if (!Schema::hasColumn('properties','land_class'))    $table->string('land_class',100)->nullable();
            if (!Schema::hasColumn('properties','usage_class'))   $table->string('usage_class',100)->nullable();
        });
    }
};
