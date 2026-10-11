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
}
