<?php

namespace Tests\Feature;

use App\Filament\Resources\Discounts\Pages\CreateDiscount;
use App\Models\Discount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DiscountFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_discount_with_just_an_image_and_no_perks_saves(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        // No perks are filled in: the site no longer shows them, so they must not block saving.
        Livewire::test(CreateDiscount::class)
            ->fillForm([
                'brand' => 'سمارت كلينك',
                'title' => 'خصم على اشتراك التطبيق',
                'image' => UploadedFile::fake()->image('banner.jpg', 1080, 1080),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $discount = Discount::firstOrFail();

        $this->assertTrue(Storage::disk('public')->exists($discount->image));
        $this->get('/')->assertSee(Storage::url($discount->image), false);
    }
}
