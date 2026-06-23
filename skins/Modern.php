<?php namespace Quendistudio\Skin\Skins;

use Backend\Skins\Standard;
use Event;
use Quendistudio\Skin\Models\Settings;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
/**
 * Standard skin information file.
 *
 * This skin uses the default paths always, there is no lookup required.
 *
 * @package winter\wn-backend-module
 * @author Alexey Bobkov, Samuel Georges
 */

class Modern extends Standard
{
    public function __construct()
    {
        parent::__construct();
		Event::listen('backend.page.beforeDisplay', function ($controller, $action, $params) {
			$controller->addCss([
                '$/quendistudio/skin/skins/enhanced/assets/css/app.min.css',
                '$/quendistudio/skin/skins/modern/assets/css/theme-'.Settings::get('theme').'.min.css',
                '$/quendistudio/skin/skins/modern/assets/css/app.min.css',
            ]);
			$controller->addJs(['$/quendistudio/skin/skins/enhanced/assets/js/app.js']);

			if($controller->isClassExtendedWith(ListController::class)) {
				$controller->addViewPath(plugins_path('/quendistudio/skin/skins/enhanced/behaviors/listcontroller/views'));
			}

			if($controller->isClassExtendedWith(FormController::class)) {
				$controller->addViewPath(plugins_path('/quendistudio/skin/skins/enhanced/behaviors/formcontroller/views'));
			}
		});
    }

    /**
     * @inheritDoc
     */
    public function getLayoutPaths()
    {
        return [
            plugins_path('/quendistudio/skin/skins/modern/layouts'),
            plugins_path('/quendistudio/skin/skins/enhanced/layouts'),
            $this->skinPath.'/layouts'
        ];
    }
}
