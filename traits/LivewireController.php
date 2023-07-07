<?php namespace Verbant\Livewire\Traits;

use Block;
use Livewire;

/**
 * trait for backend controllers. Adds a rendering function to easily render LiveWire components in the backend
 * 
 * @author Wim Verhoogt <wim@verbant.nl>
 */
trait LivewireController
{
    /**
     * renderLivewire
     *
     * @param [string] $name: component name
     * @param array $parms: variables to insert in the component.
     * @return [string] the html for the component.
     */
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
