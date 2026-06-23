<?php

namespace Quendistudio\Skin;

use BackendMenu;
use Config;
use Event;

use Backend\FormWidgets\RichEditor;
use System\Classes\PluginManager;
use System\Classes\PluginBase;
use Quendistudio\Skin\Models\Settings;

use Quendistudio\Skin\Behaviors\RichEditorExtension;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name' => 'quendistudio.skin::lang.plugin.name',
            'description' => 'quendistudio.skin::lang.plugin.description',
            'author' => 'Quendistudio',
            'icon' => 'icon-paint-brush',
            'homepage' => 'https://quendistudio.com',
        ];
    }

    public function boot()
    {
        if ($this->app->runningInBackend()) {
            // Extend RichEditor widget to support editorOptions
            RichEditor::extend(function ($widget) {
                $widget->extendClassWith(RichEditorExtension::class);
            });

            $this->applyBackendSkin();

            if (PluginManager::instance()->hasPlugin('Quendistudio.Support')) {
                $this->extendSupportPlugin();
            }
        }
    }

    /**
     * Apply the enhanced skin to the backend
     */
    protected function applyBackendSkin()
    {
        $skin = Settings::get('skin', 'enhanced');
        Config::set('cms.backendSkin', Settings::skinClassName($skin));

        /**
         * Adding change monitor ignore fields js to the form controllers
         *
         * You need to add the oc-no-change-monitor cssClass to the fields that you want to ignore the change monitor.
         */
        \Backend\Widgets\Form::extend(function ($widget) {
            $widget->addJs('$/quendistudio/skin/assets/js/change-monitor-enhanced.min.js');
        });

        Event::listen('backend.page.beforeDisplay', function ($controller) {
            $assets = $this->registerAssets();
            $controller->addJs($assets['js']);
            $controller->addCss($assets['css']);
            /**
             * Dynamically adds a helper method to render the breadcrumb navigation buttons.
             *
             * This method encapsulates all guards and rendering logic. It always returns
             * a string, which is either the rendered HTML for the buttons or an empty
             * string when the buttons should not be displayed.
             *
             * The actual HTML is delegated to the skin layout partial
             * "_breadcrumb_navigation_buttons.php" so no markup is kept here.
             */
            $controller->addDynamicMethod('getBreadcrumbNavigationButtons', function () use ($controller) {
                return \Quendistudio\Skin\Classes\BreadcrumbNavigator::makeBreadcrumbNavigationButtons($controller);
            });
        });
    }

    /**
     * Extending Support plugin
     */
    protected function extendSupportPlugin()
    {
        Event::listen('backend.menu.extendItems', function ($manager) {
            $manager->removeSideMenuItems('Quendistudio.Support', 'support', [
                'tickettypes',
                'ticketstatuses',
            ]);
        });

        Event::listen('backend.page.beforeDisplay', function ($controller) {
            if ($controller instanceof \Quendistudio\Support\Controllers\TicketTypes
                || $controller instanceof \Quendistudio\Support\Controllers\TicketStatuses) {
                BackendMenu::setContext('Quendistudio.Support', 'support', 'tickets');
            }
        });
    }

    public function registerAssets()
    {
        return [
            'css' => [
                '$/quendistudio/skin/assets/css/darkmode.min.css',
            ],
            'js' => [
                '$/quendistudio/skin/assets/js/darkmode.min.js',
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'quendistudio.skin::lang.settings.name',
                'description' => 'quendistudio.skin::lang.settings.description',
                'category' => 'QuendiStudio Plugins',
                'icon' => 'icon-palette',
                'class' => Settings::class,
                'order' => 400,
                'keywords' => 'skin',
                'permissions' => ['quendistudio.skin.administrate'],
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'quendistudio.skin.administrate' => [
                'label' => 'quendistudio.skin::lang.permissions.administrate',
                'tab' => 'quendistudio.skin::lang.plugin.name',
            ],
        ];
    }
}
