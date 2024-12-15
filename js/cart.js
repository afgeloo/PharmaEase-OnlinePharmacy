/* Set rates + misc */
var taxRate = 0.05;
var shippingRate = 15.00; 
var fadeTime = 300;

/* Assign actions */
$(document).ready(function() {
    // Handle quantity increment
    $('.quantity-increment').on('click', function() {
        var $input = $(this).siblings('input[name="quantity"]');
        var currentVal = parseInt($input.val(), 10);
        if (!isNaN(currentVal)) {
            $input.val(currentVal + 1);
        }
    });

    // Handle quantity decrement
    $('.quantity-decrement').on('click', function() {
        var $input = $(this).siblings('input[name="quantity"]');
        var currentVal = parseInt($input.val(), 10);
        if (!isNaN(currentVal) && currentVal > 1) {
            $input.val(currentVal - 1);
        }
    });

    // Allow typing in quantity field
    $('input[name="quantity"]').on('input', function() {
        var value = parseInt($(this).val(), 10);
        if (isNaN(value) || value < 1) {
            $(this).val(1);
        }
    });

  // Handle blur event
  $('input[name="quantity"]').on('blur', function() {
    if ($(this).val() === '' || parseInt($(this).val(), 10) <= 0) {
      $(this).val(1);
      updateQuantity($(this));
    }
  });

  // Set initial totals to zero
  recalculateCart();
});

/* Recalculate cart with animation */
function recalculateCart() {
  var subtotal = 0;

  /* Sum up row totals for checked items */
  $('.product').each(function() {
    if ($(this).find('.product-checkbox').is(':checked')) {
      var linePrice = parseFloat($(this).find('.product-line-price').text());
      if (!isNaN(linePrice)) {
        subtotal += linePrice;
      }
    }
  });

  /* Calculate totals */
  var tax = subtotal * taxRate;
  var shipping = (subtotal > 0 ? shippingRate : 0);
  var total = subtotal + tax + shipping;

  /* Animate totals display */
  animateValue($('#cart-subtotal'), subtotal);
  animateValue($('#cart-tax'), tax);
  animateValue($('#cart-shipping'), shipping);
  animateValue($('#cart-total'), total);

  /* Show or hide checkout button */
  if (subtotal > 0) {
    $('.checkout').fadeIn(fadeTime);
  } else {
    $('.checkout').fadeOut(fadeTime);
  }
}

/* Animate value update */
function animateValue($element, newValue) {
  const currentValue = parseFloat($element.text()) || 0;
  $({ val: currentValue }).animate(
    { val: newValue },
    {
      duration: 500, // Animation duration in ms
      easing: 'swing', // Animation easing type
      step: function(now) {
        $element.text(now.toFixed(2));
      },
      complete: function() {
        $element.text(newValue.toFixed(2)); // Ensure final value is set
      }
    }
  );
}

/* Update quantity */
function updateQuantity($input) {
  /* Calculate line price */
  var productRow = $input.closest('.product');
  var price = parseFloat(productRow.find('.product-price').text());
  var quantity = parseInt($input.val(), 10);
  var linePrice = price * quantity;

  /* Update line price display */
  productRow.find('.product-line-price').text(linePrice.toFixed(2));

  /* Optionally, send AJAX request to update cart in backend */
  var cartItemId = productRow.find('input[name="cart_item_id"]').val();
  $.post('cart_functionality/update_cart.php', {
    cart_item_id: cartItemId,
    quantity: quantity
  }, function(response) {
    // Handle response if needed
  });

  /* Recalculate cart totals */
  recalculateCart();
}

/* Remove item from cart */
function removeItem(removeButton) {
  /* Remove row from DOM and recalc cart total */
  var productRow = $(removeButton).closest('.product');
  
  console.log("Removing product row:", productRow);  // Debugging statement

  productRow.slideUp(fadeTime, function() {
    productRow.remove();  // Simplified removal for debugging
    recalculateCart();
  });
}

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.quantity-decrement').forEach(button => {
      button.addEventListener('click', function() {
          const input = this.nextElementSibling;
          if (input.value > 1) {
              input.value = parseInt(input.value) - 1;
          }
      });
  });

  document.querySelectorAll('.quantity-increment').forEach(button => {
      button.addEventListener('click', function() {
          const input = this.previousElementSibling;
          input.value = parseInt(input.value) + 1;
      });
  });
});