<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            $table->string('process_no')->nullable()->after('generated_by');
            $table->date('issue_date')->nullable()->after('process_no');
            $table->string('due_year')->nullable()->after('issue_date');
            $table->string('due_amount')->nullable()->after('due_year');
        });
    }

    public function down(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            $table->dropColumn(['process_no', 'issue_date', 'due_year', 'due_amount']);
        });
    }
};
