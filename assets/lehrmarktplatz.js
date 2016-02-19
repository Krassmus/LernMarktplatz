jQuery(document).on("change", ".lehrmarktplatz_tags li:last-child input", function () {
    if (this.value) {
        var li = jQuery(this).closest("li").clone();
        li.find("input").val("");
        jQuery(this).closest("ul").append(li);
    }
});
jQuery(document).on("change", ".lehrmarktplatz_tags li input", function () {
    if (!this.value && !jQuery(this).is("li:last-child input")) {
        if ((jQuery(this).closest("ul").children().length >= 2)) {
            jQuery(this).closest("li").remove();
        }
    }
});


jQuery(document).on("click", ".matrix a", function () {
    jQuery(this).closest(".matrix").hide("puff");
    jQuery.ajax({
        "url": STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lehrmarktplatz/market/matrixnavigation",
        "data": {
            'tags': jQuery(this).data("tags")
        },
        "type": "get",
        "dataType": "json",
        "success": function (output) {
            jQuery(".breadcrumb").replaceWith(output.breadcrumb);
            jQuery(".matrix").replaceWith(output.matrix);
            jQuery(".material_overview").html(output.materials);
        }
    });
    return false;
});

jQuery(document).on("click", ".breadcrumb a", function () {
    jQuery(".matrix").hide("scale");
    jQuery.ajax({
        "url": STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lehrmarktplatz/market/matrixnavigation",
        "data": {
            'tags': jQuery(this).data("tags")
        },
        "type": "get",
        "dataType": "json",
        "success": function (output) {
            jQuery(".breadcrumb").replaceWith(output.breadcrumb);
            jQuery(".matrix").replaceWith(output.matrix);
            jQuery(".material_overview").html(output.materials);
        }
    });
    return false;
});


//Admin
jQuery(function () {
    jQuery(".serversettings .index_server a").on("click", function () {
        var host_id = jQuery(this).closest("tr").data("host_id");
        var active = jQuery(this).is(".checked") ? 0 : 1;
        var a = this;
        jQuery.ajax({
            "url": STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lehrmarktplatz/admin/toggle_index_server",
            "data": {
                'host_id': host_id,
                'active': active
            },
            "type": "post",
            "success": function (html) {
                jQuery(a).html(html);
                if (active) {
                    jQuery(a).addClass("checked").removeClass("unchecked");
                } else {
                    jQuery(a).addClass("unchecked").removeClass("checked");
                }
            }
        });
        return false;
    });
});
