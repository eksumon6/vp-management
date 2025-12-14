<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('vp_case_no')->index();   // কেস নং ৫৫/৬৬
            $table->string('union')->index();
            $table->string('mouza')->index();
            $table->string('khatian_no')->nullable()->index();
            $table->string('jl_no')->nullable();
            $table->string('dag_no')->index();
            $table->decimal('area_decimal', 10, 4)->default(0);
            $table->string('land_class')->nullable();    // শ্রেণি
            $table->string('usage_class')->nullable();   // ব্যবহারিক শ্রেণি (চাইলে)
            $table->string('gazette_no')->nullable();

            $table->decimal('annual_rate', 12, 2)->default(0); // প্রতি বছর কত টাকা হারে লীজ

            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('properties'); }
};
