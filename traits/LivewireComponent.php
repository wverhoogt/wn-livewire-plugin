<?php namespace Verbant\Livewire\Traits;

use Livewire;

trait LivewireComponent
{
  public function onRender()
  {
    return Livewire::mount($this->name, $this->controller->vars)->html();
  }
}