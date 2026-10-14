<?php

use App\Enums\PermissionsTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Doctors are no longer deleted from the admin panel, they are suspended.
     * Renaming the permission in place keeps it on every role that held it.
     */
    public function up(): void
    {
        $this->rename('delete doctor', 'suspend doctor');
    }

    public function down(): void
    {
        $this->rename('suspend doctor', 'delete doctor');
    }

    private function rename(string $from, string $to): void
    {
        DB::table('permissions')
            ->where('name', $from)
            ->where('type', PermissionsTypeEnum::ADMIN->value)
            ->update(['name' => $to]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
