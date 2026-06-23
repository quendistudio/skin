/*
 * Change monitor: ignore fields marked with the oc-no-change-monitor class.
 * Updates the Cancel button label (Cancel / Close) based on form state.
 *
 * Usage: in fields.yaml, add to the field:
 *   cssClass: oc-no-change-monitor
 * Changes in that field will not mark the form as "changed".
 */
+function ($) {
    "use strict";

    function applyPatch() {
        if (!$().changeMonitor || !$.fn.changeMonitor.Constructor) return;
        var ChangeMonitor = $.fn.changeMonitor.Constructor;
        if (ChangeMonitor.prototype.change._ocNoChangeMonitorPatched) return;
        var originalChange = ChangeMonitor.prototype.change;
        ChangeMonitor.prototype.change = function (ev, inputChange) {
            if (ev && ev.target && $(ev.target).closest('.oc-no-change-monitor').length) {
                return;
            }
            return originalChange.apply(this, arguments);
        };
        ChangeMonitor.prototype.change._ocNoChangeMonitorPatched = true;
    }

    function updateCancelButton($form, type) {
        var $cancelButton = $form.find('.cancel-button');
        if (!$cancelButton.length) return;
        if (type === 'changed') {
            $cancelButton.text($cancelButton.data('cancel-text'));
        } else {
            $cancelButton.text($cancelButton.data('close-text'));
        }
    }

    function initCancelButtonUpdater() {
        $('form.layout').each(function () {
            if ($(this).data('change-monitor') !== true) return;
            updateCancelButton($(this), $(this).hasClass('oc-data-changed') ? 'changed' : 'unchanged');
        });
        $('form.layout').on('changed.oc.changeMonitor', function () {
            updateCancelButton($(this), 'changed');
        });
        $('form.layout').on('unchanged.oc.changeMonitor', function () {
            updateCancelButton($(this), 'unchanged');
        });
    }

    applyPatch();
    $(document).ready(initCancelButtonUpdater);
}(window.jQuery);
