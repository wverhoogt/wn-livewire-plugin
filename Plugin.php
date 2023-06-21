<?php namespace Verbant\Livewire;

use Livewire;
use Enflow\LivewireTwig\LivewireExtension;
use Backend;
use Event;
use App;
use Config;
use Verbant\Livewire\Classes\LivewireComponentCode;
use Backend\Classes\NavigationManager;
use Backend\Models\UserRole;
use System\Classes\PluginBase;
use Verbant\Livewire\Classes\Extension;
use Verbant\Livewire\Classes\Component as LivewireComponent;
use Verbant\Livewire\Classes\ThemeComponentResolver;
/**
 * Livewire Plugin Information File
 */
class Plugin extends PluginBase
{
  protected $pluginExtension;
  protected $themeComponentResolver;
  /**
   * Returns information about this plugin.
   */
  public function pluginDetails(): array
  {
    return [
      'name'        => 'verbant.livewire::lang.plugin.name',
      'description' => 'verbant.livewire::lang.plugin.description',
      'author'      => 'Verbant',
      'icon'        => 'icon-leaf'
    ];
  }

  /**
   * Register method, called when the plugin is first registered.
   */
  public function register(): void
  {
    Config::set('livewire.manifest_path', storage_path('framework/cache/livewire-components.php'));
    Config::set('livewire.class_namespace', "livewire");
    Config::set('livewire.asset_url', url(''));
    Config::set('livewire.app_url', url(''));
    // spl_autoload_register([$this, 'findComponent']);
    $this->themeComponentResolver = new ThemeComponentResolver;
    Livewire::resolveMissingComponent([$this->themeComponentResolver, 'resolve']);
    Livewire::listen('component.rendering', [$this->themeComponentResolver, 'onLivewireRender']);
  }

  /**
   * Boot method, called right before the request route.
   */
  public function boot(): void
  {
    Event::listen('cms.page.start', function (\Cms\Classes\Controller $controller) {
      $twig = $controller->getTwig();
      $twig->addExtension(new LivewireExtension);
      $this->pluginExtension = new Extension;
      $this->pluginExtension->setController($controller);
      $twig->addExtension($this->pluginExtension);
      $this->themeComponentResolver->twigEnvironment = $twig;
    });
    Event::listen('backend.menu.extendItems', function (NavigationManager $navigationManager) {
      $navigationManager->addSideMenuItems('WINTER.CMS', 'CMS', [[
        'label' => 'Livewire',
        'url' => 'javascript:;',
        'icon' => ''
      ]]);
    });
    App::bind(\Illuminate\Routing\RouteCollectionInterface::class, function ($app) { return new \Illuminate\Routing\RouteCollection; });
  }

  /**
   * Registers any frontend components implemented in this plugin.
   */
  public function registerComponents(): array
  {
    return []; // Remove this line to activate

    return [
      'Verbant\Livewire\Components\MyComponent' => 'myComponent',
    ];
  }

  /**
   * Registers any backend permissions used by this plugin.
   */
  public function registerPermissions(): array
  {
    return []; // Remove this line to activate

    return [
      'verbant.livewire.some_permission' => [
        'tab' => 'verbant.livewire::lang.plugin.name',
        'label' => 'verbant.livewire::lang.permissions.some_permission',
        'roles' => [UserRole::CODE_DEVELOPER, UserRole::CODE_PUBLISHER],
      ],
    ];
  }

  /**
   * Registers backend navigation items for this plugin.
   */
  public function registerNavigation(): array
  {
    return [
      'livewire' => [
        'label'       => 'Livewire',
        'url'         => Backend::url('verbant/livewire'),
        'icon'        => 'icon-wrench',
        'iconSvg'     => 'plugins/winter/builder/assets/images/builder-icon.svg',
        'permissions' => [],

        'sideMenu' => [
          'components' => [
            'label'       => 'components',
            'icon'        => 'icon-database',
            'url'         => 'javascript:;',
            'attributes'  => ['data-menu-item' => 'components'],
            'permissions' => []
          ],
        ]
      ]
    ];
  }
}
