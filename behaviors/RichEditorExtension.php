<?php

namespace Quendistudio\Skin\Behaviors;

use Winter\Storm\Extension\ExtensionBase;

/**
 * RichEditor extension for Winter CMS: "editorOptions" property support
 * (compatible with October CMS 3.x Advanced Editor Options documentation).
 */
class RichEditorExtension extends ExtensionBase
{
    /**
     * @var \Backend\FormWidgets\RichEditor
     */
    protected $widget;

    /**
     * RichEditorExtension constructor.
     *
     * @param \Backend\FormWidgets\RichEditor $widget
     */
    public function __construct($widget)
    {
        $widget->addJs('$/quendistudio/skin/behaviors/richeditorextension/assets/js/richeditorextension.js');

        $this->widget = $widget;

        $this->applyEditorOptionsAttribute();
    }

    /**
     * Applies the editor options to the form field.
     * Exposes the editor options to the JavaScript via a data-editor-options attribute.
     */
    protected function applyEditorOptionsAttribute(): void
    {
        $editorOptions = $this->widget->getConfig('editorOptions', null);

        if (!is_array($editorOptions) || $editorOptions === []) {
            return;
        }

        $formField = $this->getFormField();

        if (!$formField) {
            return;
        }

        // Adds a serialized data-editor-options attribute to the container of the widget
        $formField->attributes([
            'data-editor-options' => json_encode($editorOptions),
        ]);
    }

    /**
     * Retrieves the FormField instance associated with the widget via Reflection,
     * because the formField property is not public.
     *
     * @return \Backend\Classes\FormField|null
     */
    protected function getFormField()
    {
        try {
            $ref = new \ReflectionClass($this->widget);

            if (!$ref->hasProperty('formField')) {
                return null;
            }

            $prop = $ref->getProperty('formField');
            $prop->setAccessible(true);

            return $prop->getValue($this->widget);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

