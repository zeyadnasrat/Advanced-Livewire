<?php

namespace App\Livewire;

use Livewire\Component;

class Todos
{
    public $draft = 'Test Test';

    public $todos;

    public function mount()
    {
        $this->todos = collect([' One Todo ', ' Two Todo ']);
    }

    public function updatedDraft()
    {
        $this->draft = strtoupper($this->draft);
    }
    public function addTodo()
    {
        $this->todos->push($this->draft);

        $this->draft = '' ;
    }
    public function render()
    {
        return <<<'HTML'
            <div class="todos">
                <input type="text" wire:model="draft">

                <button wire:click="addTodo">Add Todo</button>

                <ul>
                    @foreach($todos as $todo)
                    <li>{{ $todo }}</li>
                    @endforeach
                </ul>
            </div>
        HTML;
    }
}
