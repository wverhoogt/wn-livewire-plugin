<?php namespace Verbant\Livewire;

use App;
use Backend;
use Backend\Classes\NavigationManager;
use Backend\Models\UserRole;
use Config;
use Enflow\LivewireTwig\LivewireExtension;
use Event;
use Livewire;
use System\Classes\PluginBase;
use System\Classes\PluginManager;
use Verbant\Livewire\Classes\ComponentResolver;
use View;

/**
 * Livewire Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name' => 'verbant.livewire::lang.plugin.name',
            'description' => 'verbant.livewire::lang.plugin.description',
            'author' => 'Verbant',
            'icon' => 'icon-leaf',
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register(): void
    {
        Config::set('livewire.manifest_path', storage_path('framework/cache/livewire-components.php'));
        Config::set('livewire_plugin.component_path', storage_path('framework/cache/plugin-components.php'));
        Config::set('livewire.class_namespace', "livewire");
        Config::set('livewire.asset_url', url(''));
        Config::set('livewire.app_url', url(''));
        App::singleton(ComponentResolver::class, function ($app) {
            return new ComponentResolver;
        });
        $this->componentResolver = App::make(ComponentResolver::class);
        Livewire::resolveMissingComponent([$this->componentResolver, 'resolve']);
        Livewire::listen('component.rendering', [$this->componentResolver, 'onLivewireRender']);
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot(): void
    {
        $pd = collect(PluginManager::instance()->getRegistrationMethodValues('registerLivewireComponents'));
        $this->componentResolver->livewireComponents = $pd->reduce(function ($c, $e) {return $c + $e;}, []);
        Event::listen('cms.page.start', function (\Cms\Classes\Controller $controller) {
            $twig = $controller->getTwig();
            $twig->addExtension(new LivewireExtension);
        });
        Event::listen('backend.menu.extendItems', function (NavigationManager $navigationManager) {
            $navigationManager->addSideMenuItems('winter.cms', 'cms', [[
                'label' => 'Livewire',
                'url' => 'javascript:;',
                'icon' => '',
            ]]);
        });
        App::bind(\Illuminate\Routing\RouteCollectionInterface::class, \Illuminate\Routing\RouteCollection::class);
        View::addExtension('twig', 'twig');
        $cacheDir = Config::get('view.compiled');
        View::addNamespace('__components', $cacheDir);
        App::extend('twig.environment', function ($twig, $app) use ($cacheDir) {
            $twig->addExtension(new LivewireExtension);
            $twig->setCache($cacheDir);
            return $twig;
        });
    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return [
            'livewire' => [
                'label' => 'Livewire',
                'url' => Backend::url('verbant/livewire'),
                'icon' => 'icon-wrench',
                'iconSvg' => 'plugins/winter/builder/assets/images/builder-icon.svg',
                'permissions' => [],

                'sideMenu' => [
                    'components' => [
                        'label' => 'Components',
                        'icon' => 'icon-database',
                        'url' => 'javascript:;',
                        'attributes' => ['data-menu-item' => 'components'],
                        'permissions' => [],
                    ],
                ],
            ],
        ];
    }
}
