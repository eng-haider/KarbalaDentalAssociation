<?php

use App\Support\WhatsApp;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Company accounts sign in with their phone (stored as 9647XXXXXXXXX), so they need no email.
            $table->string('phone', 20)->nullable()->unique()->after('email');
            $table->string('email')->nullable()->change();
        });

        // Company accounts made before phone login: their company's WhatsApp number becomes the login.
        DB::table('users')
            ->where('role', 'company')
            ->whereNull('phone')
            ->whereNotNull('company_id')
            ->orderBy('id')
            ->each(function (object $user): void {
                $phone = WhatsApp::number(DB::table('companies')->where('id', $user->company_id)->value('whatsapp_phone'));

                if ($phone && ! DB::table('users')->where('phone', $phone)->exists()) {
                    DB::table('users')->where('id', $user->id)->update(['phone' => $phone]);
                }
            });
    }

    public function down(): void
    {
        // Email stays nullable: accounts created with a phone only have none to restore.
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropColumn('phone');
        });
    }
};
