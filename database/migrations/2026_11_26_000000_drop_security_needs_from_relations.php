<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Security needs were removed from relations (model and UI) in April 2024,
     * but the columns were left behind and still showed up in Excel exports.
     */
    private array $columns = [
        'security_need_c',
        'security_need_i',
        'security_need_a',
        'security_need_t',
        'security_need_auth',
    ];

    public function up(): void
    {
        $existing = array_values(array_filter(
            $this->columns,
            fn (string $column) => Schema::hasColumn('relations', $column)
        ));

        if ($existing !== []) {
            Schema::table('relations', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }

    public function down(): void
    {
        Schema::table('relations', function (Blueprint $table) {
            $table->integer('security_need_c')->nullable()->after('importance');
            $table->integer('security_need_i')->nullable()->after('security_need_c');
            $table->integer('security_need_a')->nullable()->after('security_need_i');
            $table->integer('security_need_t')->nullable()->after('security_need_a');
            $table->integer('security_need_auth')->nullable()->after('security_need_t');
        });
    }
};
