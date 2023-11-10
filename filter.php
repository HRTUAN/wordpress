<?php
// Tệp Chứa form để có thể filter. 
// Khi Thay đổi lựa chọn và submit, tham số "orderby" mới sẽ được thêm vào URL.
global $wp_query;
$countProducts = $wp_query->post_count;
$orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'date_desc'; // Sắp xếp theo mặc định là "date_desc"
$selectedTags = isset($_GET['tags']) ? (array) $_GET['tags'] : array();
echo get_template_directory_uri() . '/ecommerce/inc/ajaxs-product.php';
$selectedCategories = isset($_GET['categories']) ? $_GET['categories'] : array();
$allTags = get_terms(
    array(
        'taxonomy' => 'product_tag',
        'hide_empty' => false,
    )
);
$allCategories = get_terms(
    array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
    )
);

$allAttributes = wc_get_attribute_taxonomies();
$attributeTerms = array();
$selectedAttributes = array();


if (!empty($allAttributes)) {
    foreach ($allAttributes as $attribute) {
        $attribute_name = $attribute->attribute_name;
        $selectedTerms = isset($_GET[$attribute_name]) ? $_GET[$attribute_name] : array();
        $selectedAttributes[$attribute_name] = $selectedTerms;
        $terms = get_terms('pa_' . $attribute->attribute_name, 'hide_empty=0');
        if (!empty($terms)) {
            $attributeTerms[$attribute->attribute_name] = $terms;
        }
    }
}


?>

<div class="filter__item">
    <div class="row">
        <div class="col-lg-4 col-md-5">
            <div class="filter__sort">
                <form action="" method="get">
                    <span>Sort By</span>
                    <select name="orderby">
                        <option <?php selected('', $orderby); ?>>Tất cả</option>
                        <option value="date_desc" <?php selected('date_desc', $orderby); ?>>Mới nhất</option>
                        <option value="date_asc" <?php selected('date_asc', $orderby); ?>>Cũ nhất</option>
                        <option value="price_desc" <?php selected('price_desc', $orderby); ?>>Giá giảm dần</option>
                        <option value="price_asc" <?php selected('price_asc', $orderby); ?>>Giá tăng dần</option>
                    </select>
                    <input type="submit" value="Filter">
                    <!-- Thêm input type="hidden" để duy trì các tham số lọc trước đó, khi có nhiều nút submit -->
                    <?php
                    foreach ($_GET as $key => $value) {
                        if ($key != 'orderby') {
                            if (is_array($value)) {
                                foreach ($value as $v) {
                                    echo '<input type="hidden" name="' . esc_attr($key) . '[]" value="' . esc_attr($v) . '">';
                                }
                            } else {
                                echo '<input name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                            }
                        }
                    }
                    ?>
                </form>

                <span>Attributes</span>
                <?php foreach ($attributeTerms as $attribute_name => $terms): ?>
                    <form action="" method="get">
                        <span>
                            <?php echo $attribute_name; ?>
                        </span>
                        <select name="<?php echo $attribute_name; ?>[]">
                            <option value="">-- Chọn
                                <?php echo $attribute_name; ?> --
                            </option>
                            <?php foreach ($terms as $term): ?>
                                <option value="<?php echo $term->slug; ?>" <?php selected(in_array($term->slug, $selectedAttributes[$attribute_name])); ?>>
                                    <?php echo $term->name; ?>
                                </option>
                            <?php endforeach; ?>
                            <!-- Lưu tham số -->
                            <?php
                            foreach ($_GET as $key => $value) {
                                if ($key != $attribute_name) {
                                    if (is_array($value)) {
                                        foreach ($value as $v) {
                                            echo '<input type="hidden" name="' . esc_attr($key) . '[]" value="' . esc_attr($v) . '">';
                                        }
                                    } else {
                                        echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                                    }
                                }
                            }
                            ?>
                        </select>
                        <input type="submit" value="Filter">
                    </form>
                <?php endforeach; ?>




                <form action="" method="get">
                    <span>Categories</span>
                    <select name="categories[]">
                        <?php foreach ($allCategories as $category): ?>
                            <option value="<?php echo $category->slug; ?>" <?php selected(in_array($category->slug, $selectedCategories)); ?>>
                                <?php echo $category->name; ?>
                            </option>
                        <?php endforeach; ?>
                        <!-- Lưu tham số -->
                        <?php
                        foreach ($_GET as $key => $value) {
                            if ($key != 'categories') {
                                if (is_array($value)) {
                                    foreach ($value as $v) {
                                        echo '<input type="hidden" name="' . esc_attr($key) . '[]" value="' . esc_attr($v) . '">';
                                    }
                                } else {
                                    echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                                }
                            }
                        }
                        ?>
                    </select>
                    <input type="submit" value="Filter">
                </form>

                <form action="" method="get">
                    <span>Tags</span>
                    <select name="tags[]">
                        <?php foreach ($allTags as $tag): ?>
                            <option value="<?php echo $tag->slug; ?>" <?php selected(in_array($tag->slug, $selectedTags)); ?>>
                                <?php echo $tag->name; ?>
                            </option>
                        <?php endforeach; ?>
                        <!-- Lưu tham số -->
                        <?php
                        foreach ($_GET as $key => $value) {
                            if ($key != 'tags') {
                                if (is_array($value)) {
                                    foreach ($value as $v) {
                                        echo '<input type="hidden" name="' . esc_attr($key) . '[]" value="' . esc_attr($v) . '">';
                                    }
                                } else {
                                    echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                                }
                            }
                        }
                        ?>
                    </select>

                    <input type="submit" value="Filter">
                    <!-- Thêm nút xóa -->
                    <a href="<?php echo esc_url(remove_all_filters_url()); ?>" class="clear-filters-button">
                        Clear Filters
                    </a>
                </form>
            </div>
        </div>
        <div class="col-lg-4 col-md-4">
            <div class="filter__found">
                <h6>Tìm thấy: <span>
                        <?php echo esc_html($countProducts); ?>
                    </span> sản phẩm</h6>
            </div>
        </div>
        <div class="col-lg-4 col-md-3">
            <div class="filter__option">
                <span class="icon_grid-2x2"></span>
                <span class="icon_ul"></span>
            </div>
        </div>
    </div>
</div>
<div id="result">
    <!-- Kết quả lọc sẽ được cập nhật ở đây -->
</div>
