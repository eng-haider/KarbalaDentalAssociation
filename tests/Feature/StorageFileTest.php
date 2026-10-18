<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageFileTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_an_upload_loads_even_without_the_public_storage_link(): void
    {
        Storage::disk('public')->put('discounts/banner.jpeg', 'jpeg-bytes');

        $this->get('/storage/discounts/banner.jpeg')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertStreamedContent('jpeg-bytes');
    }

    public function test_it_serves_nothing_outside_the_uploads(): void
    {
        Storage::disk('public')->put('osif-invitations/secret-token/card.png', 'card');
        Storage::disk('public')->put('livewire-tmp/unsaved.jpg', 'tmp');

        $this->get('/storage/news/missing.jpg')->assertNotFound();
        $this->get('/storage/news/../../.env')->assertNotFound();
        $this->get('/storage/osif-invitations/secret-token/card.png')->assertNotFound();
        $this->get('/storage/livewire-tmp/unsaved.jpg')->assertNotFound();
    }
}
