<?php namespace Verbant\Livewire\Classes;

use Illuminate\View\View;
use Livewire\Component;

/**
 * Parent class for PHP classes created for component PHP sections.
 *
 * @package winter\wn-livewire-plugin
 * @author Wim Verhoogt
 */

class LivewireComponentCode extends Component
{
    /**
     * Called by Livewire
     * @return View: the view, prepared by ComponentResolver
     */
    public function render(ComponentResolver $resolver): ?View
    {
        return $resolver->render($this);
    }
}