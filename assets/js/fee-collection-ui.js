(function ($) {
    "use strict";

    function branchFieldContainer($select) {
        var $column = $select.parents().filter(function () {
            return /(^|\s)col-(xs|sm|md|lg)-/.test(this.className) && $(this).closest(".row").length > 0;
        }).first();

        return $column.length ? $column : $select.closest(".form-group");
    }

    function hasOtherFilterFields($form, select) {
        return $form.find("select, input, textarea").filter(function () {
            if (this === select || this.type === "hidden" || this.type === "submit" || this.type === "button") {
                return false;
            }
            var name = (this.name || "").toLowerCase();
            return name !== "search" && name.indexOf("csrf") === -1;
        }).length > 0;
    }

    function branchSummary(name) {
        var $summary = $('<div class="fee-branch-summary fee-branch-summary--heading">' +
            '<span>Branch:</span>' +
            '<span class="fee-branch-summary__name"></span>' +
            '<button type="button" class="fee-branch-summary__change">Change</button>' +
            '</div>');
        $summary.find(".fee-branch-summary__name").text(name);
        return $summary;
    }

    function enhanceBranchSelect(select, collapsePanel) {
        var $select = $(select);
        if (!$select.val()) {
            return;
        }

        var $form = $select.closest("form");
        var $panel = $select.closest(".panel");
        if (!$form.length) {
            return;
        }

        var $heading = $panel.children(".panel-heading").first();
        var $field = branchFieldContainer($select);
        if (!$field.length) {
            return;
        }

        var branchName = $select.find("option:selected").text().trim();
        var $summary = $panel.length ? $heading.find(".fee-branch-summary").first() : $form.find(".fee-branch-summary").first();
        if ($summary.length) {
            $summary.find(".fee-branch-summary__name").text(branchName);
        } else {
            $summary = branchSummary(branchName);
            if ($heading.length) {
                $heading.append($summary);
            } else {
                $field.before($summary);
            }
        }

        $select.data("feeBranchEnhanced", true);
        $field.addClass("fee-branch-field is-fee-branch-collapsed");
        var $container = $panel.length ? $panel : $form;
        $container.removeClass("fee-branch-editing");
        if (collapsePanel && $panel.length && !hasOtherFilterFields($form, select)) {
            $panel.addClass("fee-branch-collapsed");
        }
    }

    $(function () {
        $('select[name="branch_id"]').each(function () {
            enhanceBranchSelect(this, true);
        });

        $(document).on("click", ".fee-branch-summary__change", function () {
            var $container = $(this).closest(".panel");
            if (!$container.length) {
                $container = $(this).closest("form");
            }
            $container.removeClass("fee-branch-collapsed").addClass("fee-branch-editing");
            $container.find('select[name="branch_id"]').first().trigger("focus");
        });

        $(document).on("change", 'select[name="branch_id"]', function () {
            enhanceBranchSelect(this, false);
        });
    });
})(jQuery);
