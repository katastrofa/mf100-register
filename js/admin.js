(function($) {
    $(document).ready(function() {
        /* tab navigation */
        $("#mf100-nav-tabs a").click(function(evt) {
            evt.preventDefault();
            var year = this.id.substr(10);
            $(".mf100-reg-table-wrap").hide();
            $("#year-" + year).show();
        });

        /* fields options display */
        $(".mf100-fields input").change(function() {
            var checked = $(this).is(":checked");
            var field = this.id.substr(6);

            $(".mf100-" + field).toggle();

            var data = {
                'action': 'mf100_update_field_visibility',
                'field': field,
                'checked': checked
            };
            $.post(ajaxurl, data, function(response) {});
        });
    });
})(jQuery);