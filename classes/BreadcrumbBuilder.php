<?php

namespace Quendistudio\Skin\Classes;

use Backend;
use BackendMenu;
use System\Classes\SettingsManager;
use Backend\Behaviors\FormController;

class BreadcrumbBuilder
{
    /**
     * Cached navigation context for the current backend request.
     * Avoids calling BackendMenu / SettingsManager multiple times.
     */
    protected static bool $navInitialized = false;
    protected static $menu = null;
    protected static $sideMenu = null;
    protected static $settingsItem = null;

    /**
     * Initializes the cached navigation context once per request.
     */
    protected static function initNavContext(): void
    {
        if (self::$navInitialized) {
            return;
        }

        self::$menu = BackendMenu::getActiveMainMenuItem();

        $context = self::$menu ? BackendMenu::getContext() : null;
        self::$sideMenu = null;
        if (self::$menu && $context && isset(self::$menu->sideMenu)) {
            $sideMenuCode = $context->sideMenuCode ?? null;
            self::$sideMenu = $sideMenuCode && isset(self::$menu->sideMenu[$sideMenuCode])
                ? self::$menu->sideMenu[$sideMenuCode]
                : null;
        }

        $settingsManager = SettingsManager::instance();
        $settingsContext = $settingsManager->getContext();
        self::$settingsItem = null;
        if ($settingsContext) {
            self::$settingsItem = $settingsManager->findSettingItem(
                $settingsContext->owner,
                $settingsContext->itemCode
            );
        }

        self::$navInitialized = true;
    }

    /**
     * Returns a breadcrumb-ready menu object tailored for the enhanced layout.
     *
     * The returned object is safe to mutate in the view without affecting
     * the global BackendMenu state.
     *
     * Structure:
     * - url          : string
     * - label        : string (already translated)
     * - sideItem     : ?object{url,label}   (filtered side menu crumb)
     * - settingsItem : ?object{url,label}   (filtered settings crumb)
     * - parentCrumb  : ?object{url,label}   (optional parent list crumb)
     * - pageTitle    : string (translated page title)
     */
    public static function getMenu(object $controller): ?object
    {
        self::initNavContext();

        if (!self::$menu) {
            return null;
        }

        // Side menu crumb candidate (with original guards)
        $sideItem = null;
        if (self::$sideMenu && self::$sideMenu->url !== self::$menu->url) {
            if (
                trans(self::$sideMenu->label) !== $controller->pageTitle
                && self::$sideMenu->label !== $controller->pageTitle
            ) {
                $sideItem = (object) [
                    'url' => self::$sideMenu->url,
                    'label' => trans(self::$sideMenu->label),
                ];
            }
        }

        // Settings item crumb candidate: only when the current main menu is the System menu
        // (Settings, Event logs, Mail templates, etc.). Otherwise SettingsManager context can be
        // stale (e.g. after visiting user settings then going to winter/user/users under Dispose),
        // which would show a wrong crumb like "User settings" in the Clients breadcrumb.
        $settingsItem = null;
        $isSystemSettingsMenu = self::$menu
            && strtolower((string) self::$menu->owner) === 'winter.system'
            && (string) self::$menu->code === 'system';
        if (self::$settingsItem && $isSystemSettingsMenu) {
            if (
                trans(self::$settingsItem->label) !== $controller->pageTitle
                && self::$settingsItem->label !== $controller->pageTitle
            ) {
                $settingsItem = (object) [
                    'url' => self::$settingsItem->url,
                    'label' => trans(self::$settingsItem->label),
                ];
            }
        }

        // Effective last URL for parent crumb deduplication
        $lastUrl = self::$menu->url;
        if ($sideItem) {
            $lastUrl = $sideItem->url;
        } elseif ($settingsItem) {
            $lastUrl = $settingsItem->url;
        }

        $parentCrumb = self::getParentCrumb($controller, $lastUrl);
        $pageTitle = self::getPageTitle($controller);

        return (object) [
            'url'          => self::$menu->url,
            'label'        => trans(self::$menu->label),
            'sideItem'     => $sideItem,
            'settingsItem' => $settingsItem,
            'parentCrumb'  => $parentCrumb,
            'pageTitle'    => $pageTitle,
        ];
    }

    /**
     * Returns the page title for the breadcrumb.
     * Controller may override via partial "breadcrumb_page_title".
     */
    public static function getPageTitle(object $controller): string
    {
        return $controller->makePartial('breadcrumb_page_title', [], false) ?: e(trans($controller->pageTitle));
    }

    // getParentCrumb is now an internal helper used by getMenu().
    // It relies on the $lastUrl already computed by getMenu().
    protected static function getParentCrumb(object $controller, ?string $lastUrl)
    {
        if (!$formConfig = $controller->formConfig) {
            return;
        }

        if (!isset($formConfig->name)) {
            return;
        }

        $parentUrl = $controller->formGetRedirectUrl();

        // Do not add a parent crumb if it would duplicate the last crumb URL.
        if ($lastUrl && $parentUrl === $lastUrl) {
            return;
        }

        $parentTitleKey = $formConfig->name . '_plural';

        return (object) [
            'url' => $parentUrl,
            'label' => trans($parentTitleKey),
        ];
    }

    /**
     * Returns the "deleted at" notice for soft-deleted records, or null when not applicable.
     */
    public static function getDeletedNotice(object $controller, $settingsItem = null)
    {
        if (!$controller->isClassExtendedWith(FormController::class)) {
            return;
        }

        $action = $controller->formGetContext();

        if ($controller->fatalError
            || !in_array($action, ['update', 'preview'], true)
            || $settingsItem
        ) {
            return;
        }

        if (!$model = $controller->formGetModel()) {
            return;
        }

        if (!$model->deleted_at) {
            return;
        }

        return trans('quendistudio.skin::lang.general.deleted_at', [
            'datetime' => Backend::dateTime($model->deleted_at, ['formatAlias' => 'dateTimeMin']),
        ]);
    }
}

