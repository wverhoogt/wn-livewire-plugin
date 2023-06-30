<?php namespace Verbant\Livewire\Traits;

use Livewire;
use Livewire\LivewireBladeDirectives;

trait LivewireComponent
{
  public function onRender()
  {
    // $componentResolver = App::make(ComponentResolver::class);
    // $lwClass = $componentResolver->livewireComponents[$this->name]['LivewireClass'];
    // $componentResolver->componentCache[$lwClass] = "{$this->name}::default";
    return Livewire::mount($this->name, $this->controller->vars)->html();
    // return LivewireBladeDirectives::livewire($this->name);
  }
}