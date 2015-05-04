(function($) {
    $(document).ready(function() {
        $("#mf100-nav-tabs a").click(function(evt) {
            evt.preventDefault();
            var year = this.id.substr(10);
            $(".mf100-reg-table-wrap").hide();
            $("#year-" + year).show();
        });
    });
})(jQuery);