<?php namespace Verbant\Livewire\Classes;

use Cms\Classes\CodeParser;
use Cms\Classes\Theme;
use Illuminate\Support\Facades\View as FacadesView;
use Illuminate\View\View;
use Winter\Storm\Support\Facades\Config as FacadesConfig;

/**
 * resolves component names to component classes and view path names. 
 * uses a table in which plugins can register their components
 * @author Wim Verhoogt <wim@verbant.nl>
 */
class ComponentResolver
{
    /**
     * @var [array] registered components
     * each value is an array with keys: 'LivewireClass', 'ViewName' and 'ViewPath' which shoud be provided by the plugin
     */
    public $livewireComponents;

    protected $componentCache = [];
    /**
     * resolves a component name to a component class, a path for the view and the view filename
     *
     * @param string $name: the component name
     * @return The class name of the component class or false if not resolved
     */
    public function resolve(string $name) : mixed
    {
        if (isset($this->livewireComponents[$name])) {
            $class = $this->livewireComponents[$name]['LivewireClass'];
            $this->componentCache[$class] = "$name::{$this->livewireComponents[$name]['ViewName']}";
            FacadesView::addNamespace($name, $this->livewireComponents[$name]['ViewPath']);
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

    /**
     * Create the View for the component. Called by Livewire 
     *
     * @param [type] $class
     * @return void
     */
    public function render($class) : ?View
    {
        $className = get_class($class);
        $v = null;
        if (isset($this->componentCache[$className])) {
            $component = $this->componentCache[$className];
            if (is_string($component)) {
                $v = FacadesView::make($component);
            } else {
                $v = $this->createViewFromString($component->markup);
            }
        }
        return $v;
    }

    /**
     * Create a view with the raw component string content.
     *
     * @param  string  $contents
     * @return View
     */
    protected function createViewFromString($contents): View
    {
        $directory = FacadesConfig::get('view.compiled');
        if (!is_file($viewFile = $directory . '/' . sha1($contents) . '.twig')) {
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            file_put_contents($viewFile, $contents);
        }
        return FacadesView::make('__components::' . basename($viewFile, '.twig'));
    }
}
