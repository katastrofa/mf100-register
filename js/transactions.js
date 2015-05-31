(function($) {
    $(document).ready(function() {
        /* tab navigation */
        $("#mf100-nav-tabs a").click(function(evt) {
            evt.preventDefault();
            var transactions = this.id.substr(5);
            $(".mf100-transactions-table-wrap").hide();
            $("#transaction-" + transactions).show();
        });

        var transaction = "";
        var $tr = {};
        function matchUser() {
            var data = {
                'action': "mf100_match_transaction",
                'user': $("#transaction-dialog-form input").val(),
                'transaction': transaction
            }

            $.post(ajaxurl, data, function() {
                dialog.dialog("close");
                $tr.remove();
                $tr = {};
            });
        }

        dialog = $("#transaction-dialog-form").dialog({
            autoOpen: false,
            height: 200,
            width: 350,
            modal: true,
            buttons: {
                "Match": matchUser,
                Cancel: function() {
                    dialog.dialog("close");
                }
            },
            close: function() {
                dialog.find("input").val("").removeClass("ui-state-error");
                transaction = "";
            }
        });

        $("a.edit").click(function(evt) {
            evt.preventDefault();
            $tr = $(this).parents("tr");
            transaction = $tr.prop("id");
            dialog.dialog("open");
        });
    });
})(jQuery);