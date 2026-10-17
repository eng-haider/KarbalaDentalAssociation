<x-filament-panels::page>
    <form wire:submit="save" style="display: grid; gap: 1.5rem;">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" size="lg">
                حفظ البيانات
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
