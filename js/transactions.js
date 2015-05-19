(function($) {
    $(document).ready(function() {
        /* tab navigation */
        $("#mf100-nav-tabs a").click(function(evt) {
            evt.preventDefault();
            var transactions = this.id.substr(5);
            $(".mf100-transactions-table-wrap").hide();
            $("#transaction-" + transactions).show();
        });
    });
})(jQuery);