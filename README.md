# Livewire for WinterCMS

[![Latest Version on Packagist](https://img.shields.io/packagist/v/enflow/livewire-twig.svg?style=flat-square)][def]

The `wn/livewire-twig` package provides the option to load Livewire components in your Twig templates.

## Installation
You can install the package via composer:

``` bash
composer require enflow/livewire-twig
```

## Usage

This package supports Livewire components in a theme, in plugins or in the backend. Each of these usages requires a specific approach:
### Theme usage
The plugin creates a menu entry in the top menubar, ¨Livewire¨ and a ¨Conponents¨ side menu entry. You can add a new component or modify an existing one. The Markup tab contains the Livewire-markup, the code tab contains the Livewire PHP-class containing the callback functions.

### Plugin component usage

#### Component registration
Component and backend Livewire components require registration, so that Livewire can find the markup and its backing class. You register these components by creating a public function registerLivewireComponents() returning information for the components as an array. Each element of this array has the following layout:
```php
    'component name' => ['LivewireClass' => Class of Livewire, 'ViewName' => 'name of view in components|controllers/component name', 'ViewPath' => "full path to this view"];
```
#### Plugin component usage
A plugin can define a component which has both Livewire-markup and a Livewire PHP-class. You can still have other component partials, and render these in the usual way using the ```twig {% partial %} ``` twig directive. The Livewire-markup is rendered by the {% component %} directive and renders the markup as defined as the ViewName during registration.

#### Plugin backend usage
A plugin can have a backend controller which supports Livewire components. The markup will probably in the controllers/controller name directory. The Livewire PHP-class could be there as well. To render the Livewire-component, call
```php
$this->renderLivewire("name", [optional arguments]); somewhere in a PHP markup-file.
```

## Installation

Add the following tags in the `head` tag, and before the end `body` tag in your page or layout.

```twig
<html>
<head>
    ...
    {{ livewireStyles() }}
</head>
<body>
    ...
    {{ livewireScripts() }}
</body>
</html>
```

### Examples

#### Theme example
Create a Livewire component by choosing ¨Livewire¨ from the top bar en then ¨Add +¨ from the sidebar. Name it counter.
On the markup tab, enter:
```twig
<div class="input-group py-3 w-25">
    <button wire:click="add" class="btn btn-outline-secondary">
        Add
    </button>
    <div class="form-control">
      {{ count }}
    </div>
    <button wire:click="subtract" class="btn btn-outline-secondary">
        Subtract
    </button>
</div>
```
On the code tab, enter:
```php
public $count = 1;

public function add()
{
  $this->count++;
}

public function subtract()
{
  $this->count--;
}
```
Note that the code editor flags an error on the top line. It doesn´t know that this code will be embedded in a class.

Create a page and on the markup tab enter:
```twig
<h3>Example</h3>
{% livewire "counter" with {'count': 2 } %}
```
And add the {{ livewireStyles() }} and {{ livewireScripts() }} on this page or on a layout, used by it.

#### Plugin component example
Create a plugin and a component named ¨lw¨ within that plugin. In the Plugin.php file include:
```php
<?php namespace YourNamespace\PluginName;

use YourNamespace\PluginName\Components\Lw;
use YourNamespace\PluginName\Components\Lw\Lw as LiveW;

class PluginName extends PluginBase
{
  public function registerComponents()
  {
    return [
      Lw::class => 'lw',
    ];
  }
  
  public function registerLivewireComponents()
  {
    return [
      'lw' => ['LivewireClass' => LiveW::class, 'ViewName' => 'default', 'ViewPath' => $this->getPluginPath() . '/components/lw'],
    ];
  }

  // other plugin code
}
```
In the components directory create a file Lw.php:
```php
<?php namespace YourNamespace\PluginName\Components;

use Cms\Classes\ComponentBase;

class Lw extends ComponentBase
{
  use \Verbant\Livewire\Traits\LivewireComponent;
  /**
   * Gets the details for the component
   */
  public function componentDetails()
  {
    return [
      'name'        => 'lw Component',
      'description' => 'No description provided yet...'
    ];
  }

  /**
   * Returns the properties provided by the component
   */
  public function defineProperties()
  {
    return [];
  }
}
```
The LivewireComponent trait adds a onRender() function which takes care of rendering the markup as  Liveewire markup.

In the components/lw directory create 2 files:
default.twig with contents:
```twig
<div class="input-group py-3 w-25">
    <button wire:click="add" class="btn btn-outline-secondary">
        Add
    </button>
    <div class="form-control">
      {{ count }}
    </div>
    <button wire:click="subtract" class="btn btn-outline-secondary">
        Subtract
    </button>
</div>
```
And Lw.php with contents:
```php
public $count = 1;

public function add()
{
  $this->count++;
}

public function subtract()
{
  $this->count--;
}
```
You can now add this component to a CMS page by dragging it from the side bar. Note that you can pass variables to the component, which will be made available to the Livewire component, like
```twig
{% component 'lw' count=5 %}
```

#### Backend example
Create a plugin and a controller named ¨lwc¨ within that plugin. In the Plugin.php file include:
```php
<?php namespace YourNamespace\PluginName;

use System\Classes\PluginBase;
use YourNamespace\PluginName\Controllers\Lwc\Lwc as LiveWc;

class Plugin extends PluginBase
{
  public function registerLivewireComponents()
  {
    return [
      'lwc' => ['LivewireClass' => LiveWc::class, 'ViewName' => 'default', 'ViewPath' => $this->getPluginPath() . '/controllers/lwc'],
    ];
  }

  // other plugin code
}
```
In the controllers directory create a file Lwc.php:
```php
<?php namespace YourNamespace\PluginName\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Lw Controller Backend Controller
 */
class Lwc extends Controller
{
  use \Verbant\Livewire\Traits\LivewireController;
  /**
     * @var array Behaviors that are implemented by this controller.
     */
    // public $implement = [
    //     LivewireController::class
    // ];

    public function __construct()
    {
        parent::__construct();
        // possible setContext() and other code
    }
    
    public function index()
    {
    }
}
```
The LivewireController trait provides the renderLivewire function.

In the controllers/lwc directory create 2 files:
lwc.twig with contents:
```twig
<div class="input-group py-3 w-25">
    <button wire:click="add" class="btn btn-outline-secondary">
        Add
    </button>
    <div class="form-control">
      {{ count }}
    </div>
    <button wire:click="subtract" class="btn btn-outline-secondary">
        Subtract
    </button>
</div>
```
And Lwc.php with contents:
```php
public $count = 1;

public function add()
{
  $this->count++;
}

public function subtract()
{
  $this->count--;
}
```
You can now add this component to a backend view by calling
```php
$this->renderLivewire("lwc", [optional value for the component]);
```
Note that for the backend you do not need to add the { livewireStyles() }} and {{ livewireScripts() }} directives.

## Todo
- [ ] Implement support for `key` tracking (probably not before Livewire v3)
- [ ] Tests.

## Testing
``` bash
$ composer test
```

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security
If you discover any security related issues, please email michel@enflow.nl instead of using the issue tracker.

## Credit
- [Wim Verhoogt](https://github.com/wverhoogt)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[def]: https://packagist.org/packages/enflow/livewire-twig