<?php namespace Verbant\Livewire\Classes;

use Cms\Classes\CmsCompoundObject;
use Livewire;

/**
 * The Component Class
 *
 * @package winter\wn-livewire-plugin
 * @author Wim Verhoogt
 */
class Component extends CmsCompoundObject
{
    /**
     * @var string The container name associated with the model, eg: pages.
     */
    protected $dirName = 'livewire';

    /**
     * Returns name of a PHP class to us a parent for the PHP class created for the object's PHP section.
     * @return string Returns the class name.
     */
    public function getCodeClassParent()
    {
        return LivewireComponentCode::class;
    }
}
