jQuery(document).ready(function ($) {
  // Handle add coupon form submission
  $("#eccm-add-coupon-form").on("submit", function (e) {
    e.preventDefault();

    let formData = $(this).serialize();
    // formData += "&action=eccm_create_coupon&nonce=" + ECCM.nonce;

    $.post(
        ECCM.ajaxurl,
        {
            action: "eccm_create_coupon",
            formData: formData,
            nonce: ECCM.nonce,
        },
        function (response) {
      if (response.success) {
        showMessage(response.data, "success");
        $("#eccm-add-coupon-form")[0].reset();
        location.reload();
      } else {
        showMessage(response.data, "error");
      }
    });
  });

  // Handle delete coupon
  $(document).on("click", ".eccm-delete-coupon", function (e) {
    e.preventDefault();

    if (!confirm("Are you sure you want to delete this coupon?")) {
      return;
    }

    const couponId = $(this).data("id");

    $.post(
      ECCM.ajaxurl,
      {
        action: "eccm_delete_coupon",
        coupon_id: couponId,
        nonce: ECCM.nonce,
      },
      function (response) {
        if (response.success) {
          showMessage(response.data, "success");
          location.reload();
        } else {
          showMessage(response.data, "error");
        }
      }
    );
  });

  function showMessage(message, type) {
    const messageClass = type === "success" ? "notice-success" : "notice-error";
    const messageHtml =
      '<div class="notice ' +
      messageClass +
      ' is-dismissible"><p>' +
      message +
      "</p></div>";
    $("#eccm-messages").html(messageHtml);

    setTimeout(function () {
      $("#eccm-messages").empty();
    }, 5000);
  }
});
