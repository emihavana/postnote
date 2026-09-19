<div>
    <!-- Success Message -->
    @if (session()->has('message'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition
        class="text-green-500 mt-2 animation-fade-out">
        {{ session('message') }}
    </div>
    @endif
    <form wire:submit="save">
        <!-- Title -->
        <div>
            <x-input-label for="title" :value="__('Title')" />
            <x-text-input wire:model="title" id="title" class="block mt-1 w-full" placeholder="Enter title..." required
                autofocus />
        </div>

        <!-- Post -->
        <div class="mt-4">
            <x-input-label for="body" :value="__('Post')" />

            <textarea wire:model="body" id="body" name="body" placeholder="Enter text..." required rows="4" class="w-full px-3 py-2.5 text-base text-gray-900 placeholder-gray-400
                       border border-gray-300 rounded-lg
                       focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500"></textarea>
        </div>

        <div class="flex items-center justify-end mb-4 mt-4">
            <x-primary-button class="ms-3">
                {{ __('Create Post') }}
            </x-primary-button>


        </div>
    </form>
</div>