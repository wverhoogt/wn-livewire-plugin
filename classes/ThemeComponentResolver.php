<?php namespace Verbant\Livewire\Classes;

use ReflectionClass;
use ReflectionProperty;
use App;
use Cms\Classes\Theme;
use Cms\Classes\CodeParser;


class ThemeComponentResolver
{
  public static $instance;
  public $twigEnvironment;
  public $componentCache = [];

  public function __construct()
  {
    static::$instance = $this;
  }

  public function resolve(string $name)
  {
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
    if ($this->twigEnvironment == null) {
      $this->twigEnvironment = App::make('twig.environment.cms');
    }
    $className = get_class($class);
    $component = $this->componentCache[$className];
    $this->twigEnvironment->getLoader()->setObject($component);
    $template = $this->twigEnvironment->load($component->getFilePath());
    $p = (new ReflectionClass($class))->getProperties(ReflectionProperty::IS_PUBLIC);
    $props = [];
    foreach (array_filter($p, fn($e) => $e->class === $className) as $ee) {
      $n = $ee->name;
      $props[$n] = $class->$n;
    }
    $componentContent = $template->render($props);
    $class->view = $componentContent;
  }
}