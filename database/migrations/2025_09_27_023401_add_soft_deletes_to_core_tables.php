<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['properties','lessees','leases','notices'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                if (!Schema::hasColumn($t->getTable(), 'deleted_at')) {
                    $t->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['properties','lessees','leases','notices'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                if (Schema::hasColumn($t->getTable(), 'deleted_at')) {
                    $t->dropSoftDeletes();
                }
            });
        }
    }
};
