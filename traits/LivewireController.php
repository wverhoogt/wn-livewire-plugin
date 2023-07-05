<?php namespace Verbant\Livewire\Traits;

use Block;
use Livewire;

trait LivewireController
{
  public function renderLivewire($name, $parms = [])
  {
    if (!isset($this->vars['livewireInjected'])) {
      Block::append('head', Livewire::styles());
      Block::append('head', Livewire::scripts());
      $this->vars['livewireInjected'] = true;
    }
    return Livewire::mount($name, $parms)->html();
  }
}