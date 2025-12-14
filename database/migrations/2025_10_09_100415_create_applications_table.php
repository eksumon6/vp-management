<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lease_id')->nullable()->constrained()->nullOnDelete();

            // 'renewal' | 'ownership_change'
            $table->string('type', 32);

            // আবেদন তারিখ
            $table->date('app_date');

            // সংক্ষিপ্ত নোট
            $table->text('note')->nullable();

            // মূল আবেদন পিডিএফ (required)
            $table->string('application_pdf');

            // renewal: সর্বশেষ DCR পিডিএফ (optional)
            $table->string('dcr_pdf')->nullable();

            // ownership change: অন্যান্য প্রমাণক দলিলাদি (PDF list)
            $table->json('extra_docs')->nullable();

            // optional: কে বানিয়েছে
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['type','app_date']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('applications');
    }
};
