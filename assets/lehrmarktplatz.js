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
