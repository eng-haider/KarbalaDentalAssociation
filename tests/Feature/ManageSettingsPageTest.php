<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_settings_page_renders_with_the_heic_diagnostics(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageSettings::class)
            ->assertSuccessful()
            ->assertSee('حالة المحرّكات');
    }
}
