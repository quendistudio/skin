/*
 * Extension RichEditor WinterCMS : "editorOptions" property support
 * (compatible doc OctoberCMS 3.x - Advanced Editor Options).
 *
 * it intercepts the call to Froala to merge the options provided via data-editor-options (injected by RichEditorExtension.php).
 */
+function ($) { "use strict";

    if (!$.fn.froalaEditor) {
        return;
    }

    var originalFroalaEditor = $.fn.froalaEditor;

    $.fn.froalaEditor = function () {
        var args = Array.prototype.slice.call(arguments);

        if (typeof args[0] === 'object' && args[0] !== null && !this.data('froala.editor')) {
            var $container = this.closest('[data-control="richeditor"]');
            if ($container.length) {
                var extra = $container.data('editorOptions');
                if (extra) {
                    if (typeof extra === 'string') {
                        try { extra = JSON.parse(extra); } catch (e) { extra = null; }
                    }
                    if (extra && typeof extra === 'object') {
                        args[0] = $.extend(true, {}, args[0], extra);
                    }
                }
            }
        }

        return originalFroalaEditor.apply(this, args);
    };

}(window.jQuery);
