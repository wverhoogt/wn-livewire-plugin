<?php namespace Verbant\Livewire\Traits;

use Enflow\LivewireTwig\LivewireExtension;
use Livewire\Livewire;
use Winter\Storm\Support\Facades\Block;

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
            $ext = app(LivewireExtension::class);
            Block::append('head', $ext->livewireStyles());
            Block::append('head', $ext->livewireScripts());
            $this->vars['livewireInjected'] = true;
        }
        $key = $parms['key'] ?? null;
        return Livewire::mount($name, $parms, $key);
    }
}
