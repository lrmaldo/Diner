<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear tabla permissions si no existe
        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug');
                $table->timestamps();

                $table->unique('name');
                $table->unique('slug');
            });
        } else {
            // Verificar que tenga las columnas necesarias
            Schema::table('permissions', function (Blueprint $table) {
                if (! Schema::hasColumn('permissions', 'guard_name')) {
                    $table->string('guard_name')->default('web')->after('slug');
                }
            });
        }

        // Crear tabla roles si no existe
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug');
                $table->timestamps();

                $table->unique('name');
                $table->unique('slug');
            });
        } else {
            // Verificar que tenga las columnas necesarias
            Schema::table('roles', function (Blueprint $table) {
                if (! Schema::hasColumn('roles', 'guard_name')) {
                    $table->string('guard_name')->default('web')->after('slug');
                }
            });
        }

        // Crear tabla permission_role si no existe
        if (! Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permission_id')->constrained()->onDelete('cascade');
                $table->foreignId('role_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['permission_id', 'role_id']);
            });
        }

        // Crear tabla permission_user si no existe
        if (! Schema::hasTable('permission_user')) {
            Schema::create('permission_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permission_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['permission_id', 'user_id']);
            });
        }

        // Crear tabla role_user si no existe
        if (! Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['role_id', 'user_id']);
            });
        }

        // Verificar que la tabla users tenga la columna role
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->nullable()->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No eliminamos tablas para evitar problemas con datos existentes
    }
};
