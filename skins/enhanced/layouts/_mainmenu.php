<script>window.skinI18n = { darkTheme: <?= json_encode(trans('quendistudio.skin::lang.general.dark_theme')) ?> };</script>
<?php
$activeItem = BackendMenu::getActiveMainMenuItem();
$mySettings = System\Classes\SettingsManager::instance()->listItems('mysettings');
$navbarMode = Backend\Models\BrandSetting::get('menu_mode', 'inline');
?>

<nav class="navbar control-toolbar navbar-mode-<?= $navbarMode ?>" id="layout-mainmenu" role="navigation">
    <div class="toolbar-item toolbar-primary">
        <div data-control="toolbar" data-use-native-drag="true">
            <a class="menu-toggle" href="javascript:;">
                <span class="menu-toggle-icon">
                    <i class="icon-bars"></i>
                </span>
                <span class="menu-toggle-title">
                    <?= $activeItem ? e(trans($activeItem->label)) : 'CMS' ?>
                </span>
            </a>

            <ul class="nav mainmenu-nav">
                <?php foreach (BackendMenu::listMainMenuItems() as $key => $item): ?>
                    <?php
                        $isActive = BackendMenu::isMainMenuItemActive($item);
                        $isDropdown = !in_array($key, ['WINTER.BUILDER.BUILDER' , 'WINTER.CMS.CMS']) && !empty($item->sideMenu) && count($item->sideMenu) > 1;
                    ?>
                    <li class="<?= $isDropdown ? 'dropdown dropdown-fixed' : null ?> <?= $isActive ? 'active' : null ?> svg-icon-container svg-active-effects">
                        <a href="<?= $item->url ?>" class="<?= $isDropdown ? 'dropdown-toggle' : null ?>">
                            <span class="nav-icon">
                                <?php if ($item->iconSvg): ?>
                                    <img
                                        src="<?= Url::asset($item->iconSvg) ?>"
                                        class="svg-icon" loading="lazy" width="30" height="30" />
                                <?php endif ?>

                                <i class="<?= $item->iconSvg ? 'svg-replace' : null ?> <?= $item->icon ?>"></i>
                            </span>
                            <span class="nav-label">
                                <?= e(trans($item->label)) ?>
                            </span>
                        </a>
                        <span
                            class="counter <?= !$item->counter ? 'empty' : null ?>"
                            data-menu-id="<?= e($item->code) ?>"
                            <?php if ($item->counterLabel): ?>
                                title="<?= e(trans($item->counterLabel)) ?>"
                            <?php endif ?>
                        >
                            <?= e($item->counter) ?>
                        </span>
                        <?php if ($isDropdown): ?>
                            <?php
                                $context = BackendMenu::getContext();
                            ?>
                            <ul class="dropdown-menu" role="menu">
                                <li class="dropdown-container">
                                    <ul>
                                        <?php foreach ($item->sideMenu as $sideItemCode => $sideItem): ?>
                                            <li
                                                class="<?= BackendMenu::isSideMenuItemActive($sideItem) ? 'active' : null ?>"
                                                <?= Html::attributes($sideItem->attributes) ?>  role="presentation"
                                            >
                                                <a role="menuitem" tabindex="-1" href="<?= $sideItem->url ?>">
                                                    <span class="nav-icon">
                                                        <?php if ($sideItem->iconSvg): ?>
                                                            <img class="svg-icon" src="<?= Url::asset($sideItem->iconSvg) ?>">
                                                        <?php endif ?>

                                                        <i class="<?= $sideItem->iconSvg ? 'svg-replace' : null ?> <?= $sideItem->icon ?>"></i>
                                                    </span>
                                                    <span class="nav-label">
                                                        <?= e(trans($sideItem->label)) ?>
                                                    </span>
                                                </a>
                                                <span
                                                    class="counter <?= $sideItem->counter === null ? 'empty' : null ?>"
                                                    data-menu-id="<?= e($context->mainMenuCode.'/'.$sideItemCode) ?>"
                                                    <?php if ($sideItem->counterLabel): ?>
                                                        title="<?= e(trans($sideItem->counterLabel)) ?>"
                                                    <?php endif ?>
                                                >
                                                    <?= e($sideItem->counter) ?>
                                                </span>
                                            </li>
                                        <?php endforeach ?>
                                    </ul>
                                </li>
                            </ul>
                        <?php endif ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
    </div>
    <div class="toolbar-item toolbar-item-account">
        <ul class="mainmenu-toolbar">
            <?php foreach (BackendMenu::listQuickActionItems() as $item): ?>
                <li class="mainmenu-quick-action with-tooltip">
                    <a
                        href="<?= $item->url ?>"
                        title="<?= e(trans($item->label)) ?>"
                        <?= Html::attributes($item->attributes) ?>
                    >

                        <?php if ($item->iconSvg): ?>
                            <img
                                src="<?= Url::asset($item->iconSvg) ?>"
                                class="svg-icon" loading="lazy" width="20" height="20" />
                        <?php endif ?>

                        <i class="<?= $item->iconSvg ? 'svg-replace' : null ?> <?= $item->icon ?>"></i>
                    </a>
                </li>
            <?php endforeach ?>
            <li class="mainmenu-account with-tooltip">
                <a
                    href="javascript:;" onclick="$.wn.layout.toggleAccountMenu(this)"
                    title="<?= e(trans('backend::lang.account.signed_in_as', ['full_name' => $this->user->full_name])) ?>">
                    <img
                        src="<?= $this->user->getAvatarThumb(90, ['mode' => 'crop', 'extension' => 'png']) ?>"
                        class="account-avatar" loading="lazy" width="90" height="90" />
                </a>
                <div class="mainmenu-accountmenu">
                    <ul>
                        <?php foreach ($mySettings as $category => $items): ?>
                            <?php foreach ($items as $item): ?>
                                <li>
                                    <a href="<?= $item->url ?>">
                                        <?= e(trans($item->label)) ?>
                                    </a>
                                </li>
                            <?php endforeach ?>
                            <li class="divider"></li>
                        <?php endforeach ?>

                        <li>
                            <a href="<?= Backend::url('backend/auth/signout') ?>">
                                <?php if (\BackendAuth::isImpersonator()): ?>
                                    <?= e(trans('backend::lang.account.stop_impersonating')) ?>
                                <?php else: ?>
                                    <?= e(trans('backend::lang.account.sign_out')) ?>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</nav>
