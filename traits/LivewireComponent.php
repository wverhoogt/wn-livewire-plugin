<?php namespace Verbant\Livewire\Traits;

use Livewire;
/**
 * Trait to add a default onRender to plugin components. Called by Livewire
 */
trait LivewireComponent
{
    /**
     * @param string $name: the name of the component as used in the plugin´s registerLivewireComponents function
     *
     * @return string: the Livewire HTML
     */
    public function onRender()
    {
        return Livewire::mount($this->name, array_merge($this->controller->vars, $this->properties))->html();
    }
}
