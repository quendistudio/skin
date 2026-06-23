<?= $this->makeLayoutPartial('breadcrumb_buttons', [], false) ?>
<?php
use Quendistudio\Skin\Classes\BreadcrumbBuilder;

// Breadcrumb menu tailored for this layout, independent of global state.
$menu = BreadcrumbBuilder::getMenu($this);
// The "deleted at" notice must not depend on the menu.
$deletedNotice = BreadcrumbBuilder::getDeletedNotice($this);
?>
<?php if ($menu): ?>
<ul>
    <li><a href="<?= $menu->url ?>"><?= e($menu->label) ?></a></li>
    <?php if ($menu->sideItem): ?>
        <li><a href="<?= $menu->sideItem->url ?>"><?= e($menu->sideItem->label) ?></a></li>
    <?php elseif ($menu->settingsItem): ?>
        <li><a href="<?= $menu->settingsItem->url ?>"><?= e($menu->settingsItem->label) ?></a></li>
    <?php endif; ?>
    <?php if ($menu->parentCrumb): ?>
        <li><a href="<?= $menu->parentCrumb->url ?>"><?= e($menu->parentCrumb->label) ?></a></li>
    <?php endif; ?>
    <li id="breadcrumb-page-title"><?= $menu->pageTitle ?></li>
</ul>
<?php endif; ?>
<?php if ($deletedNotice): ?>
    <p class="flash-message static error rounded-0"><?= $deletedNotice ?></p>
<?php endif; ?>
