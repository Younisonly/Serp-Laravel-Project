<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE notifications n
            JOIN shops s ON s.id = n.owner_id
            SET n.owner_id = s.owner_id, n.owner_type = "user"
            WHERE n.owner_type = "shop"
        ');

        DB::statement('
            DELETE n2 FROM notifications n1
            JOIN notifications n2
                ON n1.owner_id = n2.owner_id
                AND n1.type = n2.type
                AND n1.owner_type = n2.owner_type
                AND ABS(TIMESTAMPDIFF(SECOND, n1.created_at, n2.created_at)) < 5
                AND n1.id > n2.id
        ');
    }

    public function down(): void
    {
        // Irreversible — old shop-referenced notifications are already orphaned
    }
};
