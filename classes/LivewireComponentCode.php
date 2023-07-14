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
    protected $view;

    /**
     * Called by Livewirw
     * @return View: the view, prepared by ComponentResolver
     */
    public function render()
    {
        return $this->view;
    }

    /**
     * Setter for ComponentResolver to prepare the view
     * the standard view resolver in Livewire doesn´t play nice with WinterCms
     *
     * @param View $view
     * @return void
     */
    public function setView(View $view)
    {
        $this->view = $view;
    }
}