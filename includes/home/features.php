<section class="featured-section py-5">

    <div class="container">

        <div class="text-center mb-5">

            <span class="section-tag">
                ☕ Our Menu
            </span>

            <h2 class="section-title">
                Signature Coffee
            </h2>

            <p class="section-subtitle">
                Crafted from premium beans and prepared by expert baristas.
            </p>

        </div>

        <div class="row g-4">

            <?php

            $stmt = $conn->prepare("SELECT * FROM products LIMIT 3");
            $stmt->execute();

            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($products as $product):

            ?>

            <div class="col-lg-4">

                <div class="coffee-card shadow-sm">

                    <!-- Clickable Image -->
                    <a href="product.php?id=<?php echo $product['id']; ?>">

                        <div class="coffee-img">

                            <img src="assets/images/menu/<?php echo htmlspecialchars($product['image']); ?>"
                                 alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                 class="img-fluid">

                        </div>

                    </a>

                    <div class="coffee-body">

                        <!-- Clickable Product Name -->
                        <h3>

                            <a href="product.php?id=<?php echo $product['id']; ?>"
                               class="product-link">

                                <?php echo htmlspecialchars($product['product_name']); ?>

                            </a>

                        </h3>

                        <p>

                            <?php echo htmlspecialchars($product['description']); ?>

                        </p>

                        <div class="coffee-rating">

                            ⭐⭐⭐⭐⭐

                        </div>

                        <div class="coffee-footer">

                            <h4>

                                $<?php echo number_format($product['price'],2); ?>

                            </h4>

                            <button
                                class="btn btn-coffee add-cart-btn"
                                data-id="<?php echo $product['id']; ?>">

                                <i class="fa-solid fa-cart-shopping"></i>

                                Add to Cart

                            </button>

                        </div>

                    </div>

                </div>

            </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>