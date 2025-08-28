<?php
/**
 * Plugin Name: 守成店舗地図
 * Description: WordPressのカスタム投稿タイプで管理された店舗情報をGoogleマップに表示するプラグインです。
 * Version: 1.0.4
 * Author: Your Name
 * License: GPLv2 or later
 * Text Domain: shusei-local-map
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// プラグイン有効化時にカスタム投稿タイプとACFフィールドグループを登録
register_activation_hook(__FILE__, 'slm_activate_plugin');
function slm_activate_plugin() {
    slm_register_custom_post_type();
    flush_rewrite_rules();
}

// カスタム投稿タイプ 'shusei-local-map' を登録
function slm_register_custom_post_type() {
    $labels = [
        'name'                  => _x('守成店舗地図', 'Post Type General Name', 'shusei-local-map'),
        'singular_name'         => _x('店舗情報', 'Post Type Singular Name', 'shusei-local-map'),
        'menu_name'             => __('守成店舗地図', 'shusei-local-map'),
        'all_items'             => __('すべての店舗情報', 'shusei-local-map'),
        'add_new_item'          => __('新しい店舗情報を追加', 'shusei-local-map'),
        'add_new'               => __('新規追加', 'shusei-local-map'),
        'edit_item'             => __('店舗情報を編集', 'shusei-local-map'),
        'new_item'              => __('新しい店舗情報', 'shusei-local-map'),
        'view_item'             => __('店舗情報を見る', 'shusei-local-map'),
        'search_items'          => __('店舗情報を検索', 'shusei-local-map'),
        'not_found'             => __('見つかりませんでした', 'shusei-local-map'),
        'not_found_in_trash'    => __('ゴミ箱にはありませんでした', 'shusei-local-map'),
    ];
    $args = [
        'label'                 => __('守成店舗地図', 'shusei-local-map'),
        'labels'                => $labels,
        'supports'              => ['title'],
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-location-alt',
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true, // REST APIで利用可能にする
    ];
    register_post_type('shusei-local-map', $args);
}
add_action('init', 'slm_register_custom_post_type');

// ACFフィールドグループをプラグイン内で定義
function slm_register_acf_fields() {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group([
            'key' => 'group_slm_map_fields',
            'title' => '店舗情報',
            'fields' => [
                [
                    'key' => 'field_slm_latitude',
                    'label' => '緯度',
                    'name' => 'latitude',
                    'type' => 'number',
                    'instructions' => '店舗の緯度を入力してください。',
                    'required' => 1,
                    'step' => 'any',
                ],
                [
                    'key' => 'field_slm_longitude',
                    'label' => '経度',
                    'name' => 'longitude',
                    'type' => 'number',
                    'instructions' => '店舗の経度を入力してください。',
                    'required' => 1,
                    'step' => 'any',
                ],
                [
                    'key' => 'field_slm_shop_image_pin',
                    'label' => '画像1 (ピン画像)',
                    'name' => 'pin_image',
                    'type' => 'image',
                    'instructions' => '地図上に表示するイラストピンの画像をアップロードしてください。',
                    'return_format' => 'url',
                    'preview_size' => 'medium',
                ],
                [
                    'key' => 'field_slm_shop_image_panel',
                    'label' => '画像2 (パネル内画像)',
                    'name' => 'panel_image',
                    'type' => 'image',
                    'instructions' => '詳細パネル内に表示する店舗の画像をアップロードしてください。',
                    'return_format' => 'url',
                    'preview_size' => 'medium',
                ],
                [
                    'key' => 'field_slm_shop_name',
                    'label' => '店舗名',
                    'name' => 'shop_name',
                    'type' => 'text',
                    'instructions' => '店舗名を入力してください。',
                ],
                [
                    'key' => 'field_slm_contact_person',
                    'label' => '担当者',
                    'name' => 'contact_person',
                    'type' => 'text',
                    'instructions' => '担当者の名前を入力してください。',
                ],
                [
                    'key' => 'field_slm_description',
                    'label' => '説明文',
                    'name' => 'description',
                    'type' => 'textarea',
                    'instructions' => '店舗の説明を入力してください。',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'shusei-local-map',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => ['the_content'],
            'active' => true,
            'description' => '',
        ]);
    }
}
add_action('acf/init', 'slm_register_acf_fields');

// オプションページの追加
add_action('admin_menu', 'slm_add_options_page');
function slm_add_options_page() {
    add_submenu_page(
        'edit.php?post_type=shusei-local-map',
        __('守成クラブ設定', 'shusei-local-map'),
        __('守成クラブ設定', 'shusei-local-map'),
        'manage_options',
        'shusei-local-map-settings',
        'slm_render_options_page'
    );
}

// オプションページのHTMLをレンダリング
function slm_render_options_page() {
    ?>
    <div class="wrap">
        <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
        <form action="options.php" method="post">
            <?php
            settings_fields('slm_options_group');
            do_settings_sections('shusei-local-map-settings');
            submit_button();
            ?>
        </form>
        <hr>
        <h3>プラグインの使い方</h3>
        <p>以下の手順で地図をサイトに表示できます。</p>
        <ol>
            <li><strong>Google Maps APIキーの設定</strong><br>
                このページの「APIキー」欄に、取得したAPIキーを入力して保存してください。</li>
            <li><strong>地図の中心座標と拡大率の設定</strong><br>
                地図の中心となる緯度、経度、および拡大率を設定してください。</li>
            <li><strong>店舗情報の追加</strong><br>
                管理画面メニューの「守成店舗地図」から、「新規追加」を選び、店舗情報を入力します。</li>
            <li><strong>地図の埋め込み</strong><br>
                地図を表示したい投稿や固定ページに、以下のショートコードを貼り付けてください。<br>
                <code>[shusei_map]</code></li>
            <li><strong>デバッグのヒント</strong><br>
                地図が表示されない場合は、ブラウザの「開発者ツール」（通常はF12キー）を開き、「Console」（コンソール）タブにエラーが表示されていないか確認してください。</li>
        </ol>
    </div>
    <?php
}

// オプションページのフィールド登録
add_action('admin_init', 'slm_settings_init');
function slm_settings_init() {
    register_setting('slm_options_group', 'slm_api_key');
    register_setting('slm_options_group', 'slm_center_lat');
    register_setting('slm_options_group', 'slm_center_lng');
    register_setting('slm_options_group', 'slm_zoom_level');

    add_settings_section(
        'slm_api_section',
        __('Google Maps API設定', 'shusei-local-map'),
        'slm_api_section_callback',
        'shusei-local-map-settings'
    );

    add_settings_field(
        'slm_api_key_field',
        __('Google Maps APIキー', 'shusei-local-map'),
        'slm_api_key_field_callback',
        'shusei-local-map-settings',
        'slm_api_section'
    );

    add_settings_field(
        'slm_center_lat_field',
        __('中心緯度', 'shusei-local-map'),
        'slm_center_lat_field_callback',
        'shusei-local-map-settings',
        'slm_api_section'
    );

    add_settings_field(
        'slm_center_lng_field',
        __('中心経度', 'shusei-local-map'),
        'slm_center_lng_field_callback',
        'shusei-local-map-settings',
        'slm_api_section'
    );
    
    add_settings_field(
        'slm_zoom_level_field',
        __('拡大率 (Zoom)', 'shusei-local-map'),
        'slm_zoom_level_field_callback',
        'shusei-local-map-settings',
        'slm_api_section'
    );
}

function slm_api_section_callback() {
    echo '<p>' . __('地図の表示に必要な設定を入力してください。', 'shusei-local-map') . '</p>';
}

function slm_api_key_field_callback() {
    $api_key = get_option('slm_api_key');
    echo '<input type="text" name="slm_api_key" value="' . esc_attr($api_key) . '" class="regular-text" placeholder="APIキーを入力">';
}

function slm_center_lat_field_callback() {
    $center_lat = get_option('slm_center_lat', '34.6978');
    echo '<input type="number" step="any" name="slm_center_lat" value="' . esc_attr($center_lat) . '" placeholder="例: 34.6978">';
}

function slm_center_lng_field_callback() {
    $center_lng = get_option('slm_center_lng', '136.421');
    echo '<input type="number" step="any" name="slm_center_lng" value="' . esc_attr($center_lng) . '" placeholder="例: 136.421">';
}

function slm_zoom_level_field_callback() {
    $zoom_level = get_option('slm_zoom_level', 10);
    echo '<input type="number" name="slm_zoom_level" value="' . esc_attr($zoom_level) . '" placeholder="例: 10">';
}

// ショートコードを登録
add_shortcode('shusei_map', 'slm_render_map_shortcode');
function slm_render_map_shortcode() {
    $api_key = get_option('slm_api_key');
    $center_lat = get_option('slm_center_lat', '34.6978');
    $center_lng = get_option('slm_center_lng', '136.421');
    $zoom_level = get_option('slm_zoom_level', 10);

    if (empty($api_key)) {
        return '<p style="color:red; font-weight:bold;">地図を表示できません。Google Maps APIキーが設定されていません。「守成クラブ設定」ページでキーを入力してください。</p>';
    }

    ob_start();
    ?>
    <style>
        .slm-map-container {
            height: 600px;
            width: 100%;
        }
        .slm-info-window-content {
            font-family: Arial, sans-serif;
            max-width: 300px;
        }
        .slm-info-window-content img {
            max-width: 100%;
            height: auto;
            display: block;
            margin-bottom: 10px;
        }
        .slm-info-window-content h3 {
            margin: 0 0 5px;
        }
        .slm-info-window-content p {
            margin: 0 0 5px;
        }
    </style>
    <div id="slm-map" class="slm-map-container"></div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mapContainer = document.getElementById('slm-map');
            if (mapContainer) {
                if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                    const script = document.createElement('script');
                    script.src = `https://maps.googleapis.com/maps/api/js?key=<?php echo esc_js($api_key); ?>&callback=initSlmMap`;
                    script.async = true;
                    script.defer = true;
                    document.head.appendChild(script);
                } else {
                    initSlmMap();
                }
            }
        });

        function initSlmMap() {
            const map = new google.maps.Map(document.getElementById('slm-map'), {
                zoom: <?php echo esc_js($zoom_level); ?>,
                center: { lat: <?php echo esc_js($center_lat); ?>, lng: <?php echo esc_js($center_lng); ?> }
            });

            const infoWindow = new google.maps.InfoWindow();
            
            const restApiUrl = '<?php echo esc_url(rest_url('wp/v2/shusei-local-map?per_page=100&_embed')); ?>';

            fetch(restApiUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(posts => {
                    if (!Array.isArray(posts) || posts.length === 0) {
                        console.error('No posts found or API returned an empty array.');
                        return;
                    }

                    posts.forEach(post => {
                        if (post.acf) {
                            const { latitude, longitude, pin_image, panel_image, shop_name, contact_person, description } = post.acf;
                            
                            // 緯度と経度の誤入力に対応するため、parseFloatで数値に変換
                            const position = { lat: parseFloat(latitude), lng: parseFloat(longitude) };

                            if (!isNaN(position.lat) && !isNaN(position.lng)) {
                                const icon = {
                                    url: pin_image,
                                    scaledSize: new google.maps.Size(40, 40)
                                };

                                const marker = new google.maps.Marker({
                                    position: position,
                                    map: map,
                                    icon: icon,
                                    title: shop_name
                                });

                                const contentString = `
                                    <div class="slm-info-window-content">
                                        <h3>${shop_name}</h3>
                                        ${panel_image ? `<img decoding="async" src="${panel_image}" alt="${shop_name}">` : ''}
                                        <p><strong>担当者:</strong> ${contact_person}</p>
                                        <p><strong>説明:</strong> ${description}</p>
                                    </div>
                                `;

                                marker.addListener('click', () => {
                                    infoWindow.setContent(contentString);
                                    infoWindow.open(map, marker);
                                });
                            }
                        }
                    });
                })
                .catch(error => console.error('Error fetching data:', error));
        }
    </script>
    <?php
    return ob_get_clean();
}