var Chef = {
    init: function() {
        this.productImagePreview();
        this.quantityControl();
    },
    
    productImagePreview: function() {
        $('.product-images li').on('click', function() {
            var src = $(this).find('img').attr('src');
            $('.product-images .preview img').attr('src', src);
        });
    },
    
    quantityControl: function() {
        $('[data-quantity]').each(function() {
            var $this = $(this);
            var $quantityTarget = $this.find('[data-quantity-target]');
            var $quantityMinus = $this.find('[data-quantity-minus]');
            var $quantityPlus = $this.find('[data-quantity-plus]');
            var quantity = parseInt($quantityTarget.val(), 10);

            $quantityMinus.on('click', function() {
                if (quantity > 1) {
                    quantity--;
                    $quantityTarget.val(quantity);
                }
            });

            $quantityPlus.on('click', function() {
                quantity++;
                $quantityTarget.val(quantity);
            });

            $quantityTarget.on('input', function() {
                var value = parseInt($quantityTarget.val(), 10);
                if (!isNaN(value) && value > 0) {
                    quantity = value;
                }
            });

            $quantityTarget.on('blur', function() {
                if ($quantityTarget.val() === '' || parseInt($quantityTarget.val(), 10) <= 0) {
                    quantity = 1;
                    $quantityTarget.val(quantity);
                }
            });
        });
    },
    
    menuToggle: function() {
        $(document).on('click', '#menu .trigger', function() {
            // Toggle open and close icons
            $(this).find('img').each(function() {
                if ($(this).hasClass('hidden')) {
                    $(this).removeClass('hidden');
                } else {
                    $(this).addClass('hidden');
                }
            });
            
            // Toggle menu links
            $(this).siblings('.links').stop(true, true).slideToggle(200);
            
            // Toggle open class
            $('#menu').toggleClass('open');
       });
    },
    
    misc: function() {
        // Misc stuff
        
        for (var i = 1; i <= 3; i++) {
            $('.product').parent().eq(0).clone().appendTo('.product-list');
        }
    }
};

$(function() {
    Chef.init();
});

(function () {
    "use strict";
    var jQueryPlugin = (window.jQueryPlugin = function (ident, func) {
        return function (arg) {
            if (this.length > 1) {
                this.each(function () {
                    var $this = $(this);

                    if (!$this.data(ident)) {
                        $this.data(ident, func($this, arg));
                    }
                });

                return this;
            } else if (this.length === 1) {
                if (!this.data(ident)) {
                    this.data(ident, func(this, arg));
                }

                return this.data(ident);
            }
        };
    });
})();

(function () {
    "use strict";
    function Guantity($root) {
        const element = $root;
        const quantity = $root.attr("data-quantity");
        const quantity_target = $root.find("[data-quantity-target]");
        const quantity_minus = $root.find("[data-quantity-minus]");
        const quantity_plus = $root.find("[data-quantity-plus]");a
        const quantity_confirm = $root.find("[data-quantity-confirm]");
        var quantity_ = parseInt(quantity_target.val(), 10);
        $(quantity_minus).click(function () {
            quantity_ = Math.max(1, --quantity_);
            quantity_target.val(quantity_);
        });
        $(quantity_plus).click(function () {
            quantity_target.val(++quantity_);
            quantity_ = parseInt(quantity_target.val(), 10);
        });
    }
    $.fn.Guantity = jQueryPlugin("Guantity", Guantity);
    $("[data-quantity]").Guantity();
})();

