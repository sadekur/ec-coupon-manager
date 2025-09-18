jQuery(document).ready(function ($) {
  // Handle add coupon form submission
  $("#eccm-add-coupon-form").on("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    // The nonce is no longer a separate field, it's a header.
    // The server-side code handles verification automatically.
    
    $.ajax({
      url: ECCM.apiurl + '/coupons',
      method: 'POST',
      dataType: 'json',
      contentType: 'application/json',
      data: JSON.stringify(data),
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', ECCM.nonce);
      },
      success: function(response) {
        showMessage(response.message, "success");
        $("#eccm-add-coupon-form")[0].reset();
        location.reload();
      },
      error: function(response) {
        const errorMsg = response.responseJSON && response.responseJSON.message ? response.responseJSON.message : 'An error occurred.';
        showMessage(errorMsg, "error");
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
    
    $.ajax({
      url: ECCM.apiurl + '/coupons/' + couponId,
      method: 'DELETE',
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', ECCM.nonce);
      },
      success: function(response) {
        showMessage(response.message, "success");
        location.reload();
      },
      error: function(response) {
        const errorMsg = response.responseJSON && response.responseJSON.message ? response.responseJSON.message : 'An error occurred.';
        showMessage(errorMsg, "error");
      }
    });
  });
  function showMessage(message, type) {
    const messageClass = type === "success" ? "notice-success" : "notice-error";
    const messageHtml =
      `<div class="notice ${messageClass} is-dismissible"><p>${message}</p></div>`;
    $("#eccm-messages").html(messageHtml);
    setTimeout(function () {
      $("#eccm-messages").empty();
    }, 5000);
  }
});