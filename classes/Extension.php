<?php namespace Verbant\Livewire\Classes;

use Livewire;
use Block;
use Event;
use SystemException;
use Cms\Classes\Theme;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFilter as TwigSimpleFilter;
use Twig\TwigFunction as TwigSimpleFunction;
use System\Twig\Engine;
use Cms\Classes\Controller;
use Cms\Classes\CmsException;
use Cms\Classes\ComponentManager;
use Verbant\Livewire\Classes\Component as LivewireComponent;

/**
 * The CMS Twig extension class implements the basic CMS Twig functions and filters.
 *
 * @package winter\wn-cms-module
 * @author Alexey Bobkov, Samuel Georges
 */
class Extension extends TwigExtension
{
  protected Component $component;
  /**
   * The instanciated CMS controller
   */
  protected Controller $controller;

  /**
   * Sets the CMS controller instance
   */
  public function setController(Controller $controller)
  {
      $this->controller = $controller;
  }

  /**
   * Gets the CMS controller instance
   */
  public function getController(): Controller
  {
      return $this->controller;
  }

  /**
   * Returns an array of functions to add to the existing list.
   */
  public function getFunctions(): array
  {
    return [
      new TwigSimpleFunction('component', [$this, 'componentFunction']),
    ];
  }

  /**
   * Returns an array of token parsers this extension provides.
   */
  public function getTokenParsers(): array
  {
    return [
      new ComponentTokenParser,
    ];
  }

  /**
   * Renders the requested component with the provided parameters. Optionally throw an exception if the component cannot be found
   */
  public function componentFunction(string $name, array $parameters = [], bool $throwException = false): string
  {
    return $this->renderComponent($name, $parameters, $throwException);
  }

  /**
   * Renders a requested component.
   * The framework uses this method internally.
   *
   * @param string $name The view to load.
   * @param array $parameters Parameter variables to pass to the view.
   * @param bool $throwException Throw an exception if the component is not found.
   * @throws SystemException If the component cannot be found
   * @return mixed component contents or false if not throwing an exception.
   */
  public function renderComponent($name, $parameters = [], $throwException = true)
  {
    $vars = $this->controller->vars;
    $this->controller->vars = array_merge($this->controller->vars, $parameters);
    $lw = Livewire::mount($name, $this->controller->vars);
    $html = $lw->html();
    $this->controller->vars = $vars;
    return $html;
  }
}