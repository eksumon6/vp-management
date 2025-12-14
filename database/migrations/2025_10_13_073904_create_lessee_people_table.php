<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lessee_people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lessee_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('father_name', 150)->nullable();
            $table->string('nid', 50)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->text('address')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['name','nid','mobile']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessee_people');
    }
};
