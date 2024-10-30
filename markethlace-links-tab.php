<?php
/*
 * Woocommerce plugin
 * Создание кастомной вкладки в карточке товара с возможностью добавления кнопок (2 штуки) на сторонние маркетплейсы
 *
 * Работает со всеми видами товаров */


// Добавляем кастомную вкладку "Ссылки на маркетплейсы" для всех типов товаров
add_filter('woocommerce_product_data_tabs', 'add_marketplace_links_tab', 10, 1);
function add_marketplace_links_tab($tabs) {
    $tabs['marketplace_links'] = array(
        'label'    => __('Ссылки на маркетплейсы', 'woocommerce'),
        'target'   => 'marketplace_links_data', // ID контейнера вкладки
        'class'    => array('show_if_simple', 'show_if_variable', 'show_if_grouped', 'show_if_external'), // Для всех типов товаров
        'priority' => 21, // Позиция вкладки
    );
    return $tabs;
}
// Добавляем поля для ввода ссылок на маркетплейсы в кастомную вкладку
add_action('woocommerce_product_data_panels', 'add_marketplace_links_fields');
function add_marketplace_links_fields(): void
{
    ?>
    <div id="marketplace_links_data" class="panel woocommerce_options_panel">
        <div class="options_group">
            <?php
            woocommerce_wp_text_input(
                array(
                    'id' => 'external_marketplace_link_1',
                    'label' => __('Ссылка на Маркетплейс 1', 'woocommerce'),
                    'placeholder' => 'https://marketplace1.com/product-link',
                    'description' => __('Укажите ссылку на товар на первом маркетплейсе', 'woocommerce'),
                    'desc_tip' => true,
                    'type' => 'url',
                )
            );

            woocommerce_wp_text_input(
                array(
                    'id' => 'external_marketplace_link_2',
                    'label' => __('Ссылка на Маркетплейс 2', 'woocommerce'),
                    'placeholder' => 'https://marketplace2.com/product-link',
                    'description' => __('Укажите ссылку на товар на втором маркетплейсе', 'woocommerce'),
                    'desc_tip' => true,
                    'type' => 'url',
                )
            );
            ?>
        </div>
    </div>
    <?php
}

// Сохранение значений метаполей
add_action('woocommerce_process_product_meta', 'save_external_links_metabox');
function save_external_links_metabox($post_id): void
{
    if (isset($_POST['external_marketplace_link_1'])) {
        update_post_meta($post_id, 'external_marketplace_link_1', esc_url_raw($_POST['external_marketplace_link_1']));
    }
    if (isset($_POST['external_marketplace_link_2'])) {
        update_post_meta($post_id, 'external_marketplace_link_2', esc_url_raw($_POST['external_marketplace_link_2']));
    }
}

// Отключаем кнопку «Добавить в корзину» для всех типов товаров
add_filter('woocommerce_is_purchasable', 'disable_add_to_cart_for_all_products', 10, 2);
function disable_add_to_cart_for_all_products($is_purchasable, $product): bool
{
    return false; // Отключаем покупку для всех товаров
}

// Добавляем кастомные кнопки вместо кнопки «Добавить в корзину» на странице товара
add_action('woocommerce_single_product_summary', 'add_custom_marketplace_buttons', 30);
function add_custom_marketplace_buttons(): void
{
    global $product;

    // Получаем ссылки из метаполей
    $marketplace_link_1 = get_post_meta($product->get_id(), 'external_marketplace_link_1', true);
    $marketplace_link_2 = get_post_meta($product->get_id(), 'external_marketplace_link_2', true);
    // Проверяем, что ссылки установлены, и выводим кастомные кнопки
    if ($marketplace_link_1 || $marketplace_link_2) {
        echo '<div class="custom-marketplace-buttons" style="margin-top: 20px;">';

        if ($marketplace_link_1) {
            echo '<a href="' . esc_url($marketplace_link_1) . '" target="_blank" class="button alt" style="margin-right: 10px;">Купить на Маркетплейс 1</a>';
        }

        if ($marketplace_link_2) {
            echo '<a href="' . esc_url($marketplace_link_2) . '" target="_blank" class="button alt">Купить на Маркетплейс 2</a>';
        }

        echo '</div>';
    }
}