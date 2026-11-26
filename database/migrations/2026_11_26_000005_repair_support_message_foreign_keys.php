<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ([
            'conversation_id' => 'support_conversations',
            'sender_user_id' => 'users',
        ] as $column => $referencedTable) {
            $exists = DB::selectOne(
                'SELECT 1 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME = ?
                 AND COLUMN_NAME = ?
                 AND REFERENCED_TABLE_NAME = ?
                 LIMIT 1',
                ['support_messages', $column, $referencedTable]
            );

            if (! $exists) {
                $constraint = 'support_messages_'.$column.'_foreign';
                DB::statement(
                    'ALTER TABLE support_messages ADD CONSTRAINT `'.$constraint.'` FOREIGN KEY (`'.$column.'`) REFERENCES `'.$referencedTable.'` (`id`) ON DELETE CASCADE'
                );
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE support_messages DROP FOREIGN KEY support_messages_sender_user_id_foreign');
        DB::statement('ALTER TABLE support_messages DROP FOREIGN KEY support_messages_conversation_id_foreign');
    }
};
