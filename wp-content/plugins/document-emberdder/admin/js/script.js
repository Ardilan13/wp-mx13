(function ($) {
    $(document).ready(function () {

    // import data
      $(document).on("click", ".ppv_import_data", function (e) {
        e.preventDefault();
        $.ajax({
          url: ppvAdmin.ajaxUrl,
          data: {
            action: "ppv_import_data",
          },
          success: function (data) {
            const result = JSON.parse(data);
            if (result.success === true) {
              location.href = location.href + "?ppv-import=success";
            }
          },
        });
      });
  
      // copy to clipboard
      $(".ppv_front_shortcode input").on("click", function (e) {
        e.preventDefault();
  
        let shortcode = $(this).parent().find("input")[0];
        shortcode.select();
        shortcode.setSelectionRange(0, 30);
        document.execCommand("copy");
        $(this).parent().find(".htooltip").text("Copied Successfully!");
      });
  
      $(".ppv_front_shortcode input").on("mouseout", function () {
        $(this).parent().find(".htooltip").text("Copy To Clipboard");
      });
    });
  })(jQuery);
  