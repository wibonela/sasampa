<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('tin')->nullable()->after('address');
            $table->string('vrn')->nullable()->after('tin');
            $table->string('efd_serial_number')->nullable()->after('vrn');
            $table->string('efd_uin')->nullable()->after('efd_serial_number');
            $table->boolean('efd_enabled')->default(false)->after('efd_uin');
            $table->string('efd_environment')->default('sandbox')->after('efd_enabled');
            $table->timestamp('efd_registered_at')->nullable()->after('efd_environment');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'tin', 'vrn', 'efd_serial_number', 'efd_uin',
                'efd_enabled', 'efd_environment', 'efd_registered_at',
            ]);
        });
    }
};
