<?php namespace Verbant\Livewire\Classes;

use ReflectionClass;
use ReflectionProperty;
use App;
use Illuminate\Support\Str;
use Illuminate\View\View;
use View as ViewFactory;
use Config;
use Cms\Classes\Theme;
use Cms\Classes\Controller;
use Cms\Classes\CodeParser;
use Enflow\LivewireTwig\LivewireExtension;

class ComponentResolver
{
  
  public $livewireComponents;
  public $componentCache = [];

  public function resolve(string $name)
  {
    if (isset($this->livewireComponents[$name])) {
      $class = $this->livewireComponents[$name]['LivewireClass'];
      $this->componentCache[$class] = "$name::{$this->livewireComponents[$name]['ViewName']}";
      ViewFactory::addNamespace($name, $this->livewireComponents[$name]['ViewPath']);
      return $class;
    }
    if (($component = Component::loadCached(Theme::getActiveTheme(), $name)) === null) {
      return false;
    }
    $parser = new CodeParser($component);
    $data = $parser->parse();
    if (!class_exists($data['className'], false)) {
      require_once $data['filePath'];
    }
    $this->componentCache[$data['className']] = $component;
    return $data['className'];
  }

  public function onLivewireRender($class)
  {
    $className = get_class($class);
    if (isset($this->componentCache[$className])) {
      $component = $this->componentCache[$className];
      if (is_string($component)) {
        $class->setView(ViewFactory::make($component));
      } else {
        $class->setView($this->createViewFromString($component->markup));
      }
    }
  }

    /**
   * Create a view with the raw component string content.
   *
   * @param  string  $contents
   * @return View
   */
  protected function createViewFromString($contents) : View
  {
    $directory = Config::get('view.compiled');
    if (! is_file($viewFile = $directory.'/'.sha1($contents).'.twig')) {
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        file_put_contents($viewFile, $contents);
    }
    return ViewFactory::make('__components::' . basename($viewFile, '.twig'));
  }
}