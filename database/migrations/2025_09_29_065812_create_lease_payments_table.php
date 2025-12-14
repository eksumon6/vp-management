<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lease_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained('leases')->cascadeOnDelete();

            $table->unsignedSmallInteger('from_year');  // last_paid_year + 1
            $table->unsignedSmallInteger('to_year');    // যে সন পর্যন্ত নবায়ন হলো
            $table->decimal('amount_paid', 12, 2);      // মোট টাকা

            $table->string('receipt_no')->nullable();   // ডিসিয়ার/রশিদ নং
            $table->date('receipt_date')->nullable();
            $table->date('approved_at')->nullable();    // AC(Land) অনুমোদনের তারিখ

            $table->string('scan_path')->nullable();    // স্ক্যান কপি (PDF/JPG)

            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('lease_payments'); }
};
