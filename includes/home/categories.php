<section class="categories-section py-5">

    <div class="container">

        <div class="text-center mb-5">

            <span class="section-tag">
                Our Categories
            </span>

            <h2 class="section-title">
                Browse Our Menu
            </h2>

            <p class="section-subtitle">
                Discover our delicious coffee and desserts.
            </p>

        </div>

        <div class="row">

            <?php

            $sql = "SELECT * FROM categories ORDER BY id ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

           $categoryIcons = [
    'hot coffee'      => 'fa-solid fa-mug-hot',
    'cold coffee'     => 'fa-solid fa-glass-water',
    'tea'             => 'fa-solid fa-mug-hot',
    'desserts'        => 'fa-solid fa-cake-candles',
    'bakery'          => 'fa-solid fa-bread-slice',
    'special drinks'  => 'fa-solid fa-martini-glass',
    'drinks'          => 'fa-solid fa-glass-water',
    'smoothies'       => 'fa-solid fa-blender',
    'breakfast'       => 'fa-solid fa-egg',
    'lunch'           => 'fa-solid fa-utensils',
    'snacks'          => 'fa-solid fa-cookie',
    'fast food'       => 'fa-solid fa-burger',
];

            foreach($categories as $category){
                $categoryKey = strtolower(trim($category['category_name']));
                $categoryIcon = $categoryIcons[$categoryKey] ?? '🍽️';

            ?>

            <div class="col-md-4 col-lg-3 mb-4">

                <div class="category-card text-center">

                    <div class="category-icon">
    <i class="<?= $categoryIcon ?>"></i>
</div>

                    <h4>

                        <?php echo htmlspecialchars($category['category_name']); ?>

                    </h4>

                </div>

            </div>

            <?php } ?>

        </div>

    </div>

</section>
