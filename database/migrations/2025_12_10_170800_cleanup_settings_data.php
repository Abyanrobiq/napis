<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, let's check if user_id column exists
        if (!Schema::hasColumn('settings', 'user_id')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            });
        }

        // Clean up duplicate entries and assign proper user_id
        $duplicates = DB::select("
            SELECT `key`, COUNT(*) as count 
            FROM settings 
            GROUP BY `key` 
            HAVING count > 1
        ");

        foreach ($duplicates as $duplicate) {
            $records = DB::table('settings')
                ->where('key', $duplicate->key)
                ->orderBy('created_at', 'desc')
                ->get();

            // Keep the latest record, delete others
            $keep = $records->first();
            $toDelete = $records->slice(1);

            foreach ($toDelete as $record) {
                DB::table('settings')->where('id', $record->id)->delete();
            }

            // Update the kept record to have user_id if it doesn't
            if (is_null($keep->user_id)) {
                DB::table('settings')
                    ->where('id', $keep->id)
                    ->update(['user_id' => 1]); // Default to user 1, adjust as needed
            }
        }

        // Now fix the unique constraint
        try {
            // Drop old unique constraint if exists
            Schema::table('settings', function (Blueprint $table) {
                $table->dropUnique(['key']);
            });
        } catch (Exception $e) {
            // Constraint might not exist, continue
        }

        // Add new unique constraint
        try {
            Schema::table('settings', function (Blueprint $table) {
                $table->unique(['key', 'user_id'], 'settings_key_user_unique');
            });
        } catch (Exception $e) {
            // Constraint might already exist, continue
        }

        // Make user_id not nullable
        Schema::table('settings', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_key_user_unique');
            $table->unique('key');
        });
    }
};