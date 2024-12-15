<?php
session_start();

require 'includes/dbconnect.php';

// Updated SQL query with joins
$sql = "SELECT p.product_id, p.product_name, p.category_id, p.product_description, p.product_price, p.store, c.category_name, pi.image_name_1
        FROM products p
        JOIN product_categories c ON p.category_id = c.category_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id
        WHERE p.category_id IN (3, 4, 5)
        ORDER BY RAND()";



$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/home.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="css/reset.css">
  <link rel="shortcut icon" type="image/png" href="assets/PharmaEaseLogo.png">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
  <title>Welcome to PharmaEase</title>
  <script src="js/products.js"></script>
</head>
<body>
<div class="container">
<?php include 'includes/header.php'; ?>

<!-- mask layout -->
    <section class="section-grid grid-six-col gallery" id="gallery">
        <div class="card">
        </div>
        <div class="card gallery-slider">
      <div class="gallery-slider__container">
        <figure class="gallery-slider__slide">
          <figcaption class="content">
          </figcaption>
        </figure>
        <figure class="gallery-slider__slide">
          <figcaption class="content">
            <h2 class="card__title">Welcome to PharmaEase</h2>
          </figcaption>
        </figure>
        <figure class="gallery-slider__slide">
          <figcaption class="content">
            <h2 class="card__title">Find the latest deals</h2>
          </figcaption>
        </figure>
        <figure class="gallery-slider__slide">
          <figcaption class="content">
            <h2 class="card__title">PharmaEase: The Medicine that’s Always Within Reach.</h2>
          </figcaption>
        </figure>
      </div>
      <div class="gallery__dots dots">
        <span class="dot active"></span>
        <span class="dot"></span>
        <span class="dot"></span>
        <span class="dot"></span>
      </div>
    </div>
    <div class="card">
      <button class="slider__btn--grid gallery-next"><img src="assets/slider/rightArrow.png" alt="right arrow"></button>
      <button class="slider__btn--grid gallery-prev"><img src="assets/slider/leftArrow.png" alt="left arrow"></button>
    </div>
      </section>
    <div class="details">
      <img
        src="assets/PharmaEaseLogoHD.png"
        ;
        alt="mask"
      />
      <div class="stay-home">
          <h2>PharmaEase</h2>
      </div>
          <p>PharmaEase is an online pharmacy designed to empower local pharmacies by providing a digital avenue to offer their services and products. PharmaEase ensures that individuals can access essential medications conveniently, especially during emergencies when immediate assistance may not be available. By connecting pharmacies directly with consumers, PharmaEase enhances accessibility to healthcare and supports the modernization of local pharmaceutical services. </p>
    </div>
    <!-- Deals of the day -->
    <br><h2>Top Products</h2>
    <div class="product-container">
    <div id="grid-selector">
               <div id="grid-menu">
                      View:
                   <ul>           	   
                       <li class="largeGrid"><a href=""></a></li>
                       <li class="smallGrid"><a class="active" href=""></a></li>
                   </ul>
               </div>
               
               <ion-icon name="cart-outline"></ion-icon>
        </div>
        
        <div id="grid">
        <?php
        // Check if products are available
        if ($result->num_rows > 0) {
            // Loop through each product and display it
            while($row = $result->fetch_assoc()) {
              $images = []; 
              // Updated SQL query to fetch all image names
              $image_sql = "SELECT image_name_1, image_name_2, image_name_3 FROM product_images WHERE product_id = " . $row['product_id'];
              $image_result = $conn->query($image_sql);
              if($img = $image_result->fetch_assoc()) {
                  if (!empty($img['image_name_1'])) {
                      $images[] = $img['image_name_1']; // Main Image
                  }
                  if (!empty($img['image_name_2'])) {
                      $images[] = $img['image_name_2'];
                  }
                  if (!empty($img['image_name_3'])) {
                      $images[] = $img['image_name_3'];
                  }
              }
              ?>
                
                <div class="product">
                    <!-- Product Info -->
                    <div class="info-large">
                    <h4><?php echo htmlspecialchars($row['product_name']); ?></h4>

                        <?php if (!empty($row['category_name'])): ?>
                        <span><?php echo htmlspecialchars($row['category_name']); ?></span>
                        <?php endif; ?>                
                
                        <div class="price-big">
                            ₱<?php echo number_format($row['product_price'], 2); ?>
                        </div>
                
                        <h3>DESCRIPTION</h3>
                        <div class="colors-large">
                            <span><?php echo htmlspecialchars($row['product_description']); ?></span>
                        </div>
                
                        <h3>STORE</h3>
                        <div class="sizes-large">
                            <span><?php echo htmlspecialchars($row['store']); ?></span>
                        </div>
                
                        <!-- <h3>QUANTITY</h3> Added quantity heading -->
                        <div> 
                          <!-- class="quantity-control" quantity=""> -->
                            <!-- <button class="quantity-btn" quantity-minus="">
                                <svg viewBox="0 0 409.6 409.6">
                                    <g>
                                        <g>
                                            <path d="M392.533,187.733H17.067C7.641,187.733,0,195.374,0,204.8s7.641,17.067,17.067,17.067h375.467 c9.426,0,17.067-7.641,17.067-17.067S401.959,187.733,392.533,187.733z" />
                                        </g>
                                    </g>
                                </svg>
                            </button> -->
                            <!-- <input type="number" class="quantity-input" quantity-target="" value="1" step="1" min="1" max="" name="quantity">
                            <button class="quantity-btn" quantity-plus="">
                                <svg viewBox="0 0 426.66667 426.66667">
                                    <path d="m405.332031 192h-170.664062v-170.667969c0-11.773437-9.558594-21.332031-21.335938-21.332031-11.773437 0-21.332031 9.558594-21.332031 21.332031v170.667969h-170.667969c-11.773437 0-21.332031 9.558594-21.332031 21.332031 0 11.777344 9.558594 21.335938 21.332031 21.335938h170.667969v170.664062c0 11.777344 9.558594 21.335938 21.332031 21.335938 11.777344 0 21.335938-9.558594 21.335938-21.335938v-170.664062h170.664062c11.777344 0 21.335938-9.558594 21.335938-21.335938 0-11.773437-9.558594-21.332031-21.335938-21.332031zm0 0" />
                                </svg>
                            </button> -->
                        </div>
                
                        <form action="../cart/cart2.php" method="post">
                            <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($images[0]); ?>">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['product_id']); ?>">
                            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['product_name']); ?>">
                            <input type="hidden" name="category_name" value="<?php echo htmlspecialchars($row['category_name']); ?>">
                            <input type="hidden" name="product_description" value="<?php echo htmlspecialchars($row['product_description']); ?>">
                            <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($row['product_price']); ?>">
                            <input type="hidden" name="product_quantity" value="1"> <!-- Default quantity -->
                            <!-- <button type="submit" name="add_to_cart" class="add-cart-large">Add To Cart</button> -->
                        </form>
                
                        <a href="productview.php?id=<?php echo $row['product_id']; ?>" class="view_product_link">View Product Details</a>
                    </div>
                
                    <!-- 3D Product Display -->
                    <div class="make3D">
                        <div class="product-front">
                            <div class="shadow"></div>
                            <?php
                            if (!empty($images)) {
                                echo '<img src="' . htmlspecialchars($images[0]) . '" alt="Product Front Image" />';
                            } else {
                                echo '<img src="https://placehold.co/600x600" alt="Default Front Image" />';
                            }
                            ?>
                            <div class="image_overlay"></div>
                            <div class="view_gallery">View gallery</div>
                            <a href="productview.php?id=<?php echo $row['product_id']; ?>" class="view_details">View details</a>
                            <div class="stats">
                                <div class="stats-container">
                                    <span class="product_price">₱<?php echo number_format($row['product_price'], 2); ?></span>
                                    <span class="product_name"><?php echo htmlspecialchars($row['product_name']); ?></span>
                                    <br>
                                    <span class="category_name"><?php echo htmlspecialchars($row['category_name']); ?></span>
                                    <div class="product-options">
                                    <br /><strong>DESCRIPTION</strong>
                                        <span><?php echo htmlspecialchars($row['product_description']); ?></span>
                                        <strong>STORE</strong>
                                        <div class="colors">
                                            <span><?php echo htmlspecialchars($row['store']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                
                        <!-- Product Back (Carousel) -->
                        <div class="product-back">
                            <div class="shadow"></div>
                            <div class="carousel">
                                <ul class="carousel-container">
                                    <?php
                                    if (!empty($images)) {
                                        $carousel_images = array_slice($images, 0, 3); // Show up to 3 images in carousel
                                        foreach ($carousel_images as $image) {
                                            echo '<li><img src="' . htmlspecialchars($image) . '" alt="Product Image" /></li>';
                                        }
                                    } else {
                                        echo '<li><img src="/path/to/default-image.jpg" alt="Default Image" /></li>';
                                    }
                                    ?>
                                </ul>
                                <div class="arrows-perspective">
                                    <div class="carouselPrev">
                                        <div class="y"></div>
                                        <div class="x"></div>
                                    </div>
                                    <div class="carouselNext">
                                        <div class="y"></div>
                                        <div class="x"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flip-back">
                                <div class="cy"></div>
                                <div class="cx"></div>
                            </div>
                        </div>
                    </div>  
                </div>
                <?php
            }
        } else {
            echo "No products found.";
        }
        ?>
        </div>
    </div>

        <?php include 'includes/footer.php'; ?>
    </div>

    <script>
$(document).ready(function() {
    $('[quantity]').each(function() {
        var $this = $(this);
        var $quantityTarget = $this.find('[quantity-target]');
        var $quantityMinus = $this.find('[quantity-minus]');
        var $quantityPlus = $this.find('[quantity-plus]');
        var quantity = parseInt($quantityTarget.val(), 10);

        $quantityMinus.on('click', function() {
            if (quantity > 1) {
                quantity--;
                $quantityTarget.val(quantity);
                $this.closest('.info-large').find('.quantity').val(quantity); // Update hidden input
            }
        });

        $quantityPlus.on('click', function() {
            quantity++;
            $quantityTarget.val(quantity);
            $this.closest('.info-large').find('.quantity').val(quantity); // Update hidden input
        });

        $quantityTarget.on('input', function() {
            var value = parseInt($quantityTarget.val(), 10);
            if (!isNaN(value) && value > 0) {
                quantity = value;
                $this.closest('.info-large').find('.quantity').val(quantity); // Update hidden input
            }
        });

        $quantityTarget.on('blur', function() {
            if ($quantityTarget.val() === '' || parseInt($quantityTarget.val(), 10) <= 0) {
                quantity = 1;
                $quantityTarget.val(quantity);
                $this.closest('.info-large').find('.quantity').val(quantity); // Update hidden input
            }
        });
    });
});

const _ = className => document.querySelector(className);
const __ = className => document.querySelectorAll(className);
// slide width in %

class Slider {
  constructor(next, prev, slides, dots, container, slideWidth, slideIndex = 0, timer = 0) {
    Object.assign(this, {
      next,
      prev,
      slides,
      dots,
      container,
      slideWidth,
      slideIndex,
      timer
    });
  };

  // NEXT CLICK
  moveRight() {
    this.slideIndex++;
    if (this.slideIndex === this.slides.length) {
      this.slideIndex = 0;
      this.dots.forEach(dot => {
        dot.classList.remove('active');
      });
      this.dots[this.slideIndex].classList.add('active');
    } else {
      this.dots.forEach(dot => {
        dot.classList.remove('active');
      });
      this.dots[this.slideIndex].classList.add('active');
    };
    this.container.style.left = `${-this.slideWidth*this.slideIndex}%`;
  };
  nextListener() {
    this.next.addEventListener('click', () => {
      this.moveRight();
    });
  };

  // PREV CLICK
  moveLeft() {
    if (this.slideIndex <= 0) this.slideIndex = this.slides.length;
    this.slideIndex--;
    this.container.style.left = `${-this.slideWidth*this.slideIndex}%`;
    this.dots.forEach(dot => {
      dot.classList.remove('active');
    });
    this.dots[this.slideIndex].classList.add('active');
  };
  prevListener() {
    this.prev.addEventListener('click', () => {
      this.moveLeft();
    });
  };

  // DOTS CLICK
  dotSelect(e) {
    let dotsArray = Array.from(this.dots);
    this.dots.forEach(dot => {
      dot.classList.remove('active');
    });
    e.target.classList.add('active');
    let dotIndex = dotsArray.indexOf(e.target);
    this.container.style.left = `${-this.slideWidth * dotIndex}%`;
    this.slideIndex = dotIndex;
  };

  // AUTO RUN
  autoRun() {
    this.timer = setInterval(() => {
      this.moveRight();
    }, 5000); // Slow down the slider speed to 5 seconds
  };

  // STOP AUTORUN
  clearAutoRun() {
    this.next.addEventListener('mouseenter', () => {
      clearInterval(this.timer);
    });
    this.prev.addEventListener('mouseenter', () => {
      clearInterval(this.timer);
    });
    this.dots.forEach(dot => {
      dot.addEventListener('mouseenter', () => {
        clearInterval(this.timer);
      });
    });
  };

  // RESTORE AUTORUN
  restoreAutoRun() {
    this.next.addEventListener('mouseleave', () => {
      this.autoRun();
    });
    this.prev.addEventListener('mouseleave', () => {
      this.autoRun();
    });
    this.dots.forEach(dot => {
      dot.addEventListener('mouseleave', () => {
        this.autoRun();
      });
    });
  };

  // INIT SLIDER
  init() {
    this.nextListener();
    this.prevListener();
    this.dots.forEach(dot => {
      dot.addEventListener('click', (e) => {
        this.dotSelect(e);
      });
    });
    this.autoRun();
    this.clearAutoRun();
    this.restoreAutoRun();
  };
};

// INIT GALLERY SLIDER
const gallerySlider = new Slider(_('.gallery-next'), _('.gallery-prev'), __('.gallery-slider__slide'), __('.gallery__dots .dot'), _('.gallery-slider__container'), 100);
gallerySlider.init();
</script>
</body>
</html>