<?php

use App\Models\Post;
use function Livewire\Volt\computed;
use function Livewire\Volt\state;
use illuminate\Support\Str;
use function Livewire\Volt\on;
//to expand the full text of a post
state(['expanded' => []]);
$toggleExpand = function ($postId) {
    if (in_array($postId, $this->expanded)) {
        $this->expanded = array_diff($this->expanded, [$postId]);
    } else {
        $this->expanded[] = $postId;
    }
};


on(['postCreated' => function () {
    // intentionally empty — its presence is what triggers
    // Livewire to re-render this component when the event fires,
    // which causes $this->posts (the computed property) to run again
}]);

$posts = computed(fn() => Post::latest()->where('user_id', auth()->id())->get());
$deletePost = function ($postId) {
    $post = Post::find($postId);
    if ($post && $post->user_id !== auth()->id()) {
        abort(403, 'Unauthorized action.');
    }
    $post->delete();
};
?>
<div class="lg:h-screen lg:overflow-y-scroll lg:snap-y lg:snap-mandatory p-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($this->posts as $post)
        <div class="border border-gray-300 rounded p-4 flex flex-col h-full snap-start">
            <h3 class="text-lg font-semibold text-blue-500">{{ $post->title }}</h3>

            <!-- <p class="text-sm text-gray-400 mt-1">
                {{ $post->created_at->format('M d, Y') }}
            </p> -->

            <p class="text-sm text-gray-400 mt-1">
                {{ $post->created_at->diffForHumans() }}
            </p>
            <p class="flex-grow">
                @if (in_array($post->id, $this->expanded))
                {{ $post->body }}
                @else
                {{ Str::limit($post->body, 100) }}
                @endif
            </p>
            @if (Strlen($post->body) > 100)
            <button wire:click="toggleExpand({{ $post->id }})" class="text-blue-500 hover:text-blue-700">
                {{ in_array($post->id, $this->expanded) ? 'Show Less' : 'Show More' }}
            </button>
            @endif
            @if(auth()->check() && auth()->id() === $post->user_id)
            <button wire:click="deletePost({{ $post->id }})" wire:confirm="Are you sure you want to delete this post?"
                class="bg-red-500 text-white px-4 py-2 rounded mt-2 self-start">Delete</button>
            @endif
        </div>
        @endforeach
    </div>

    <!-- copyright footer -->
    <div class="text-center text-gray-500 text-sm mt-4">
        Copyright © comrade - <span x-text="new Date().getFullYear()"></span>
    </div>
</div>