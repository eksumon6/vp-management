<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lessee_id')->constrained()->cascadeOnDelete();

            $table->unsignedSmallInteger('first_year');      // প্রথম সন (যেমন 1425)
            $table->unsignedSmallInteger('last_paid_year')->nullable(); // সর্বশেষ পরিশোধিত সন (যেমন 1426)
            $table->decimal('annual_rate', 12, 2)->default(0);          // snapshot (property থেকে ডিফল্ট)
            $table->date('approved_at')->nullable();          // লীজ অনুমোদনের তারিখ

            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('leases'); }
};
