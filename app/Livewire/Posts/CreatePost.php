<?php

namespace App\Livewire\Posts;

use Livewire\Component;


class CreatePost extends Component
{
    public string $title = '';
    public string $body = '';
    public function rules()
    {
        return [
            'title' => 'required|string|max:30',
            'body' => 'required|string|max:3000',

        ];
    }
    public function save()
    {
        $this->validate();
        auth()->user()->posts()->create([
            'title' => $this->title,
            'body' => $this->body,
        ]);
        $this->reset([
            'title',
            'body'
        ]);

        session()->flash('message', 'Post created successfully.');
        $this->dispatch('postCreated');
        return $this->redirect('/dashboard');
    }

    public function render()
    {


        return view('livewire.posts.create-post');
    }
}