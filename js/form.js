(function($) {
    var replacementFormLoaded = false;

    function loadCorrectForm() {
        var replacement = ($("#nahradnik:checked").length > 0);

        if (replacement && !replacementFormLoaded) {
            $.data(document, "own-form", $("#mf100-form")[0].outerHTML);
            $("#mf100-form").replaceWith($.data(document, "replacement-form"));
            replacementFormLoaded = true;
        } else if (!replacement && replacementFormLoaded) {
            $.data(document, "replacement-form", $("#mf100-form")[0].outerHTML);
            $("#mf100-form").replaceWith($.data(document, "own-form"));
            replacementFormLoaded = false;
        }
    }

    $(document).ready(function() {
        $.data(document, "own-form", mf100Own.data);
        $.data(document, "replacement-form", mf100Original.data);
        $("#nahradnik").click(function() {
            loadCorrectForm();
        });
    });
})(jQuery);