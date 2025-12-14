<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained('leases')->cascadeOnDelete();
            $table->foreignId('generated_by')->nullable()->constrained('users'); // চাইলে nullable রাখলাম
            $table->string('file_path');
            $table->timestamp('generated_at');
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('notices'); }
};
