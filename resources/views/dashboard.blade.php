<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('All Posts') }}
        </h2>
    </x-slot>

    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <div class="">
            <livewire:posts.create-post />
        </div>

        <div class="mt-4">
            <livewire:mpesapay />
        </div>
        <div class="mt-4">
            <livewire:posts.post-list />
        </div>
    </div>
</x-app-layout>