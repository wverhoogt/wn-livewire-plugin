<?php namespace Verbant\Livewire\Classes;

use Cms\Classes\CmsCompoundObject;
use Cms\Classes\CodeParser;
use Livewire;
use Cms\Classes\Theme;

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

  public static function loadComponentClass($name)
  {
    if (str_contains($name, '\\') || ($component = Component::loadCached(Theme::getActiveTheme(), $name)) === null) {
      return false;
    }
    $parser = new CodeParser($component);
    $data = $parser->parse();
    if (!class_exists($data['className'], false)) {
      require_once $data['filePath'];
    }
    Livewire::component($name, $data['className']);
    return $component;
  }
}
