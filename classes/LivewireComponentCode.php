<?php namespace Verbant\Livewire\Classes;

use Livewire\Component;
use Cms\Classes\CodeBase;

/**
 * Parent class for PHP classes created for component PHP sections.
 *
 * @package winter\wn-livewire-plugin
 * @author Wim Verhoogt
 */

class LivewireComponentCode extends Component
{
  public $view;

  public function render()
  {
    return $this->view;
  }
}
