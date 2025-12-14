<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('property_plots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            $table->string('dag_no', 100);
            $table->string('land_class', 100)->nullable(); // ব্যবহারিক শ্রেণি নেই
            $table->decimal('area_value', 12, 4)->default(0);
            $table->enum('area_unit', ['shotok','sqft'])->default('shotok'); // শতক / বর্গফুট
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->index(['property_id','dag_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_plots');
    }
};
