<?php namespace Verbant\Livewire;

// use Backend\Classes\NavigationManager;
use Backend\Facades\Backend;
use Enflow\LivewireTwig\LivewireExtension;
use Illuminate\Support\Facades\View;
use System\Classes\PluginBase;
use System\Classes\PluginManager;
use Verbant\Livewire\Classes\ComponentResolver;
use Winter\Storm\Support\Facades\Config;
use Winter\Storm\Support\Facades\Event;

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
        Config::set('livewire_plugin.component_path', storage_path('framework/cache/plugin-components.php'));
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot(): void
    {
        $pd = collect(PluginManager::instance()->getRegistrationMethodValues('registerLivewireComponents'));
        $componentResolver = new ComponentResolver;
        $componentResolver->livewireComponents = $pd->reduce(function ($c, $e) {return $c + $e;}, []);
        // Event::listen('backend.menu.extendItems', function (NavigationManager $navigationManager) {
        //     $navigationManager->addSideMenuItems('winter.cms', 'cms', [[
        //         'label' => 'Livewire',
        //         'url' => 'javascript:;',
        //         'icon' => '',
        //     ]]);
        // });
        $this->app->bind(\Illuminate\Routing\RouteCollectionInterface::class, \Illuminate\Routing\RouteCollection::class);
        View::addExtension('twig', 'twig');
        $cacheDir = Config::get('view.compiled');
        View::addNamespace('__components', $cacheDir);
        $this->app->booted(function() use ($cacheDir) {
            $ext = new LivewireExtension;
            Event::listen('cms.page.start', function (\Cms\Classes\Controller $controller) use ($ext) {
                $twig = $controller->getTwig();
                $twig->addExtension($ext);
            });
            $this->app->extend('twig.environment', function ($twig, $app) use ($cacheDir, $ext) {
                $twig->addExtension($ext);
                $twig->setCache($cacheDir);
                return $twig;
            });
        });
        app('livewire')->resolveMissingComponent([$componentResolver, 'resolve']);
        $this->app->instance(ComponentResolver::class, $componentResolver);
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
