<?php
// Dùng hook pre_get_posts  kiểm tra nếu truy vấn chính đang ở trong danh mục sản phẩm hoặc là trang tìm kiếm.
// Nếu truy vấn thỏa mãn điều kiện, kiểm tra các tham số trong URL để xác định cách sắp xếp và 
// gửi lại truy vấn dựa trên yêu cầu của người dùng
//pre_get_posts: filter, action
add_filter('pre_get_posts', 'wp2023_theme_pre_get_posts');
function wp2023_theme_pre_get_posts($query)
{

    if ($query->is_main_query() && (is_tax('product_cat') || $query->is_search())) {
        $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'date_desc';
        $selectedTags = isset($_GET['tags']) ? (array) $_GET['tags'] : array();

        // kiểm tra tag có được truyền qua url k, có:lấy gt đc truyền, k: mảng rỗng
        $selectedCategories = isset($_GET['categories']) ? $_GET['categories'] : array();

        $allAttributes = wc_get_attribute_taxonomies();
        if (!empty($allAttributes)) {
            foreach ($allAttributes as $attribute) {
                $attribute_name = $attribute->attribute_name;
                $selectedTerms = isset($_GET[$attribute_name]) ? $_GET[$attribute_name] : array();
                if (!empty($selectedTerms)) {
                    $selectedAttributes[$attribute_name] = $selectedTerms;
                }
            }
        }

        switch ($orderby) {
            case 'date_asc':
                $query->set('orderby', 'date');
                $query->set('order', 'ASC');
                break;
            case 'price_desc':
                $query->set('orderby', 'meta_value_num');
                $query->set('meta_key', '_regular_price');
                // sang woo thì chuyển thành _regular_price hoặc _sale_price
                $query->set('order', 'DESC');
                break;
            case 'price_asc':
                $query->set('orderby', 'meta_value_num');
                $query->set('meta_key', '_regular_price');
                $query->set('order', 'ASC');
                break;
            default:
                break;
        }

        $taxQuery = array('relation' => 'AND');
        // tham số truy vấn của taxQuery, relation(Accepts 'AND', or 'OR'. Default 'AND')

        // Tags
        if (!empty($selectedTags)) {
            $tagQuery = array(
                'taxonomy' => 'product_tag',
                'field' => 'slug',
                'terms' => $selectedTags,
            );
            $taxQuery[] = $tagQuery;
            // đưa vào mảng taxQuery ở trên, cùng với relation để tạo ra điều kiện lọc
        }

        // Category
        if (!empty($selectedCategories)) {
            $categoryQuery = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $selectedCategories,
            );
            $taxQuery[] = $categoryQuery;
        }

        if (!empty($selectedAttributes)) {
            $attributeTermsQuery = array('relation' => 'AND');
            foreach ($selectedAttributes as $attribute_name => $selectedTerms) {
                $termQuery = array(
                    'taxonomy' => 'pa_' . $attribute_name,
                    'field' => 'slug',
                    'terms' => $selectedTerms,
                );
                $attributeTermsQuery[] = $termQuery;
            }
            $taxQuery[] = $attributeTermsQuery;
        }

        $query->set('tax_query', $taxQuery);
    }

    return $query;
}
