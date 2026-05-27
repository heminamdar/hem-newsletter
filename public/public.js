/* Hem Newsletter — Public JS */
(function ($) {
  'use strict';

  $(document).on('click', '.hem-nl-submit-btn', function () {
    var $btn   = $(this);
    var $wrap  = $btn.closest('.hem-nl-form-wrap');
    var $input = $wrap.find('.hem-nl-email-input');
    var $msg   = $wrap.find('.hem-nl-message');
    var email  = $input.val().trim();

    if (!email) {
      showMessage($msg, 'Please enter your email address.', false);
      $input.focus();
      return;
    }

    $btn.addClass('is-loading').prop('disabled', true);
    $msg.removeClass('is-success is-error').text('');

    $.ajax({
      url:  hemNL.ajax_url,
      type: 'POST',
      data: {
        action: 'hem_nl_subscribe',
        nonce:  hemNL.nonce,
        email:  email,
      },
      success: function (res) {
        showMessage($msg, res.message, res.success);
        if (res.success) {
          $input.val('');
        }
      },
      error: function () {
        showMessage($msg, 'Something went wrong. Please try again.', false);
      },
      complete: function () {
        $btn.removeClass('is-loading').prop('disabled', false);
      },
    });
  });

  // Allow pressing Enter in the email field
  $(document).on('keypress', '.hem-nl-email-input', function (e) {
    if (e.which === 13) {
      $(this).closest('.hem-nl-form-wrap').find('.hem-nl-submit-btn').trigger('click');
    }
  });

  function showMessage($el, message, success) {
    $el.removeClass('is-success is-error')
       .addClass(success ? 'is-success' : 'is-error')
       .text(message);
  }

}(jQuery));
