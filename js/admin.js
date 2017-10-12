(function($) {
    function bindEditLinks(selectorPrefix) {
        if (selectorPrefix.length > 0) {
            $(selectorPrefix + " a.delete").click(toggleRegistration);
            $(selectorPrefix + " a.edit").click(editUser);
        } else {
            $("a.delete").click(toggleRegistration);
            $("a.edit").click(editUser);
        }
    }

    function toggleRegistration(evt) {
        evt.preventDefault();

        var year = $(this).parents("tr").prop("class").substr(5, 4);
        var idUser = $(this).parents("tr").prop("class").substr(10);
        var data = {
            "action": "mf100_toggle_register",
            "user": idUser,
            "year": year,
            "race": "100"
        };

        $.post(ajaxurl, data, function(response) {
            $("tr.user-" + year + "-" + idUser).replaceWith(response);
            bindEditLinks("tr.user-" + year + "-" + idUser);
        });
    }

    function editUser(evt) {
        evt.preventDefault();

        var $tr = $(this).parents("tr");
        var year = $tr.prop("class").substr(5, 4);
        var idUser = $tr.prop("class").substr(10);

        $tr.find("td.editable").each(function(){
            var data = $(this).text();
            $(this).text("").append("<input type='text' class='edit' value='" + data + "' />");
        });

        $tr.find("td.name a").each(function() {
            var data = $(this).text();
            $(this).replaceWith(data);
        });

        $tr.find("td.save-edit").show();
        $("div.year-" + year + " th.save-edit").show();

        /* Canceling edit */
        $tr.find("td.save-edit .cancel").click(function() {
            var dataCancel = {
                'action': 'mf100_cancel_edit',
                'user': idUser,
                'year': year
            };

            $.post(ajaxurl, dataCancel, function(response) {
                $("tr.user-" + year + "-" + idUser).replaceWith(response);
                bindEditLinks("tr.user-" + year + "-" + idUser);
            });
        });

        /* Saving edit */
        $tr.find("td.save-edit .save").click(function() {
            var dataSave = {
                'action': 'mf100_save_edit',
                'user': idUser,
                'year': year
            };

            $tr.find("td.editable").each(function() {
                var column = $(this).data("field");
                var value = $(this).find("input").val();
                dataSave[column] = value;
            });

            $.post(ajaxurl, dataSave, function(response) {
                $("tr.user-" + year + "-" + idUser).replaceWith(response);
                bindEditLinks("tr.user-" + year + "-" + idUser);
            })
        });
    }

    $(document).ready(function() {
        /* tab navigation */
        $("#mf100-nav-tabs a").click(function(evt) {
            evt.preventDefault();
            var year = this.id.substr(10);
            $(".mf100-reg-table-wrap").hide();
            $(".year-" + year).show();
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

        bindEditLinks("");

        $("input.resend-reg").click(function(evt) {
            var year = $(this).parents("tr").prop("class").substr(5, 4);
            var idUser = $(this).parents("tr").prop("class").substr(10);
            var data = {
                "action": "mf100_resend_register_email",
                "user": idUser,
                "race": "100"
            };

            $.post(ajaxurl, data, function(response) {
                $("input.resend-" + year + "-" + idUser).replaceWith(response);
            });
        });
    });
})(jQuery);