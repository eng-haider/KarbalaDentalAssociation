<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_osif_coordinator_has_only_the_osif_management_capability(): void
    {
        $user = new User(['role' => User::ROLE_OSIF_COORDINATOR]);

        $this->assertTrue($user->canManageOsif());
        $this->assertFalse($user->isAdmin());
    }

    public function test_accounts_without_a_role_remain_administrators_for_a_safe_upgrade(): void
    {
        $this->assertTrue((new User)->isAdmin());
    }

    public function test_the_whatsapp_admin_is_an_administrator_who_alone_sends_through_the_api(): void
    {
        $user = new User(['role' => User::ROLE_WHATSAPP_ADMIN]);

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->canManageOsif());
        $this->assertTrue($user->canSendWhatsAppInvitations());
        $this->assertFalse((new User(['role' => User::ROLE_ADMIN]))->canSendWhatsAppInvitations());
        $this->assertFalse((new User(['role' => User::ROLE_OSIF_COORDINATOR]))->canSendWhatsAppInvitations());
        $this->assertSame('مدير النظام (إرسال واتساب)', User::roleOptions()[User::ROLE_WHATSAPP_ADMIN]);
    }
}
