<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->isUuidColumn()) {
            DB::table('oauth_refresh_tokens')->delete();
            DB::table('oauth_access_tokens')->delete();
            DB::table('oauth_auth_codes')->delete();
            DB::table('oauth_personal_access_clients')->delete();
            DB::table('oauth_clients')->delete();

            $driver = DB::connection()->getDriverName();

            if ($driver === 'sqlite') {
                $this->migratePassportTablesForSQLite();
            } else {
                Schema::table('oauth_access_tokens', function (Blueprint $table) {
                    $table->string('client_id', 100)->change();
                });
                Schema::table('oauth_auth_codes', function (Blueprint $table) {
                    $table->string('client_id', 100)->change();
                });
                Schema::table('oauth_clients', function (Blueprint $table) {
                    $table->string('id', 100)->change();
                });
            }
        }
    }

    private function migratePassportTablesForSQLite(): void
    {
        // SQLite ne supporte pas ALTER COLUMN fiablement — on recrée les tables

        Schema::drop('oauth_personal_access_clients');
        Schema::drop('oauth_refresh_tokens');
        Schema::drop('oauth_access_tokens');
        Schema::drop('oauth_auth_codes');
        Schema::drop('oauth_clients');

        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('name');
            $table->string('secret', 100)->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect');
            $table->tinyInteger('personal_access_client');
            $table->tinyInteger('password_client');
            $table->tinyInteger('revoked');
            $table->timestamps();
        });

        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('client_id', 100);
            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->tinyInteger('revoked');
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_auth_codes', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('client_id', 100);
            $table->text('scopes')->nullable();
            $table->tinyInteger('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('access_token_id', 100)->index();
            $table->tinyInteger('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_personal_access_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('client_id', 100);
            $table->timestamps();
        });
    }

    private function isUuidColumn(): bool
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $table = 'oauth_clients';

        return match ($driver) {
            'mysql', 'mariadb' => $this->isUuidMySQL($table),
            'pgsql' => $this->isUuidPostgreSQL($table),
            'sqlite' => $this->isUuidSQLite($table),
            default => false,
        };
    }

    private function isUuidMySQL(string $table): bool
    {
        $col = DB::selectOne("
            SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = 'id'
        ", [$table]);

        return $col && in_array(strtolower($col->DATA_TYPE), ['varchar', 'char', 'uuid']);
    }

    private function isUuidPostgreSQL(string $table): bool
    {
        $col = DB::selectOne("
            SELECT data_type FROM information_schema.columns
            WHERE table_name = ?
              AND column_name = 'id'
        ", [$table]);

        return $col && in_array(strtolower($col->data_type), ['character varying', 'uuid', 'text']);
    }

    private function isUuidSQLite(string $table): bool
    {
        $cols = DB::select("PRAGMA table_info({$table})");
        foreach ($cols as $col) {
            if ($col->name === 'id') {
                return in_array(strtoupper($col->type), ['VARCHAR', 'TEXT', 'UUID', 'CHAR']);
            }
        }

        return false;
    }
};
