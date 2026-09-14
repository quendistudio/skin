<?php
$label = object_get($this->config, 'label');

// cssClass lives on the wrapping form-group; pull icon classes onto the legend
// so wn-icon-* / oc-icon-* render inside the panel title instead of above it.
$iconClasses = [];
foreach (preg_split('/\s+/', (string) $this->formField->cssClass, -1, PREG_SPLIT_NO_EMPTY) as $class) {
    if (
        str_starts_with($class, 'wn-icon-')
        || str_starts_with($class, 'oc-icon-')
        || str_starts_with($class, 'icon-')
    ) {
        $iconClasses[] = $class;
    }
}
?>
<fieldset class="fieldset<?= $iconClasses ? ' has-icon' : '' ?>">
    <?php if ($label): ?>
        <legend<?= $iconClasses ? ' class="' . e(implode(' ', $iconClasses)) . '"' : '' ?>><?= e(trans($label)) ?></legend>
    <?php endif ?>

    <?= $this->formWidget->render(['section' => 'outside']) ?>
</fieldset>
