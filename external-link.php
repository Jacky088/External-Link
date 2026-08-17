<?php
/**
 * Plugin Name: 外链跳转插件
 * Description: 外链跳转插件是一个非常实用的WordPress插件，它可以对文章中的外链进行过滤，有效地防止追踪和提醒用户。
 * Version: 1.1.1
 * Author: 木木
 * Author URI: https://github.com/Jacky088/External-Link
 * Plugin URI: https://github.com/Jacky088/External-Link
 */

if (!defined('ABSPATH')) {
    exit;
}

function external_redirect_add_author_link($plugin_meta, $plugin_file, $plugin_data) {
    // 仅作用于当前插件（通过插件文件路径匹配）
    if ($plugin_file !== plugin_basename(__FILE__)) {
        return $plugin_meta;
    }

    $new_author = '木木';
    $new_author_url = 'https://github.com/Jacky088/External-Link';

    $plugin_meta[] = '<a href="' . esc_url($new_author_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($new_author) . '</a>';

    return $plugin_meta;
}
add_filter('plugin_row_meta', 'external_redirect_add_author_link', 20, 3);

// 在插件列表页添加「设置」操作入口，指向 CSF 设置页（顺序：停用 | 设置）
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'external_link_add_settings_link');
function external_link_add_settings_link($actions) {
    $settings_url = admin_url('admin.php?page=dmy_link_settings');
    $settings_link = '<a href="' . esc_url($settings_url) . '">' . esc_html__('设置', 'external-link') . '</a>';
    // 将设置链接追加到末尾，使顺序呈现为「停用 | 设置」
    $actions[] = $settings_link;
    return $actions;
}

if (!defined('ABSPATH')) {
    exit;
}

// 插件统一版本
function external_link_plugin_version()
{
    return "1.1.1";
}
$version = external_link_plugin_version();

// 定义插件路径常量
define('EXTERNAL_LINK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EXTERNAL_LINK_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EXTERNAL_LINK_URL', EXTERNAL_LINK_PLUGIN_URL);

// 判断当前主题是否是zibll主题或其子主题
function is_zibll_themes()
{
// 获取当前主题对象
    $current_theme = wp_get_theme();

// 检测当前主题是否是zibll主主题
    if ($current_theme->get_stylesheet() === "zibll") {
        return true;
    }

// 检测当前主题是否是zibll的子主题（父主题为zibll）
    if ($current_theme->get("Template") === "zibll") {
        return true;
    }

    // Neither // 都不是
    return false;
}

// 判断当前主题是否是WebStack导航主题或其变体
function is_webstack_themes()
{
    $current_theme = wp_get_theme();
    $stylesheet    = strtolower($current_theme->get_stylesheet());
    $template      = strtolower($current_theme->get('Template'));

    // 匹配 WebStack 主主题及常见变体（webstack、webstack-pro、webstackpro 等）
    if (preg_match('/webstack/i', $stylesheet) || preg_match('/webstack/i', $template)) {
        return true;
    }

    return false;
}

// 初始化所有功能
function external_link_init_functions() {
    // 全局配置变量
    global $external_link_config;
    $external_link_config = get_option("dmy_link_settings", []);
    
    // 记录CSF初始化状态的变量
    $csf_initialized = false;

    // 初始化CSF设置面板
    if (class_exists("CSF")) {
        $csf_initialized = external_link_init_csf_settings();
    } else {
        $csf_initialized = false;
    }

    // 添加备用菜单注册方式，确保在CSF无法正常工作时仍能显示插件入口
    if (!$csf_initialized) {
        if (!is_zibll_themes()) {
            add_action("admin_menu", "external_link_add_fallback_menu");
        }
    }
}
add_action('init', 'external_link_init_functions');

// CSF设置文件加载逻辑
if (is_zibll_themes()) {
    // 使用子比函数挂载
    require_once EXTERNAL_LINK_PLUGIN_DIR . "codestar-framework/admin-settings/external-link-settings.php";
    add_action("after_setup_theme", "external_link_settings");
} else {
    // 非子比引入必要文件
    $required_files = [
        "/codestar-framework/codestar-framework.php",
        "/codestar-framework/admin-settings/external-link-settings.php",
    ];

    // 检查Codestar Framework是否已存在
    $csf_exists = class_exists("CSF");
    foreach ($required_files as $file) {
        $full_path = EXTERNAL_LINK_PLUGIN_DIR . $file;
        // 如果是Codestar框架文件且已存在，则跳过加载
        if (
            $file === "/codestar-framework/codestar-framework.php" &&
            $csf_exists
        ) {
            continue;
        }
        // 加载其他文件
        if (file_exists($full_path)) {
            require_once $full_path;
        } else {
            error_log("外链跳转插件错误：缺少必要文件 - " . $full_path);
        }
    }
}

// 备用菜单函数
function external_link_add_fallback_menu() {
    add_menu_page(
        "外链跳转设置",
        "外链跳转",
        "manage_options",
        "external_link_fallback",
        "external_link_fallback_page",
        "dashicons-admin-links",
        59
    );
}

function external_link_fallback_page() {
    if (!current_user_can("manage_options")) {
        wp_die("您没有足够的权限访问此页面。");
    }

    $csf_loaded = class_exists("CSF") ? "已加载" : "未加载";
    echo '<div class="wrap">';
    echo "<h1>外链跳转设置</h1>";
    echo '<div class="notice notice-warning">';
    echo "<p>检测到配置面板框架未正常加载，可能是文件缺失或损坏。</p>";
    echo "<p>CSF框架状态: " . esc_html($csf_loaded) . "</p>";
    echo "<p>请检查 <code>codestar-framework/</code> 文件夹是否存在且完整。</p>";
    echo "<p>如果问题持续存在，请重新安装插件。</p>";
    echo "</div>";
    echo "</div>";
}

// 初始化CSF设置
function external_link_init_csf_settings() {
    // 子比主题下由 after_setup_theme 钩子负责调用，这里跳过避免重复
    if (is_zibll_themes()) {
        return false;
    }

    // 只有后台才执行此代码
    if (!is_admin()) {
        return false;
    }
    
    // 检查CSF是否可用
    if (!class_exists('CSF')) {
        return false;
    }
    
    // 调用设置函数
    if (function_exists('external_link_settings')) {
        external_link_settings();
        return true;
    }
    
    return false;
}



// 加载 CSS 样式
function external_link_enqueue_styles() {
    // 检查总开关状态
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        return; // 开关关闭时不加载样式
    }

    wp_enqueue_style('external-link-csf-css', plugin_dir_url(__FILE__) . 'css/external-link.css', array(), '1.0', 'all');
    
    $selected_style = isset($settings['dmy_link_style']) ? $settings['dmy_link_style'] : 'external-link-default';

    if ($selected_style) {
        $css_file_path = plugin_dir_path(__FILE__) . 'css/' . $selected_style . '.css';
        if (file_exists($css_file_path)) {
            wp_enqueue_style('external-link-custom-style', plugin_dir_url(__FILE__) . 'css/' . $selected_style . '.css', array(), filemtime($css_file_path), 'all');
        }
    }

    // 注：跳转页的样式模板（HTML 结构）由 external-link-template.php 通过
    // templates/ 目录加载，此处仅负责前台 CSS 资源入队，无需再加载函数式样式文件。
}
add_action('wp_enqueue_scripts', 'external_link_enqueue_styles');

// 加载实时换链脚本（方案A）：前端点击外链时实时换取跳转 URL
// 避免 CDN / 页面缓存导致跳转 Token 过期失效
function external_link_enqueue_live_script() {
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        return; // 总开关关闭时不加载
    }

    // 跳转页本身无需加载
    if (get_query_var('dinterception') == 1) {
        return;
    }

    $js_file_path = plugin_dir_path(__FILE__) . 'js/external-link-live.js';
    if (!file_exists($js_file_path)) {
        return;
    }

    wp_enqueue_script(
        'external-link-live',
        plugin_dir_url(__FILE__) . 'js/external-link-live.js',
        array(),
        filemtime($js_file_path),
        true
    );

    $selector = '';
    $function_type = isset($settings['dmy_link_function_type']) ? $settings['dmy_link_function_type'] : 'none';
    if ($function_type === 'circle') {
        $selector = !empty($settings['dmy_link_circle_selector']) ? $settings['dmy_link_circle_selector'] : '.topic-content';
    } elseif ($function_type === 'forums') {
        $selector = !empty($settings['dmy_link_forums_selector']) ? $settings['dmy_link_forums_selector'] : '.forum-article';
    }

    wp_localize_script('external-link-live', 'external_link_live_config', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'domain'   => wp_parse_url(home_url(), PHP_URL_HOST),
        'port'     => wp_parse_url(home_url(), PHP_URL_PORT),
        'selector' => $selector,
    ));
}
add_action('wp_enqueue_scripts', 'external_link_enqueue_live_script', 15);

// 统一URL加密函数
function external_link_encrypt_url($url) {
    $settings = get_option('dmy_link_settings');
    $method = isset($settings['dmy_link_verification_method']) ? $settings['dmy_link_verification_method'] : 'random_string';
    
    if ($method === 'aes_encryption') {
        // AES加密方式（固定IV实现）
        $key = isset($settings['dmy_link_aes_key']) ? $settings['dmy_link_aes_key'] : '';
        if (empty($key)) {
            // Preserve the documented fallback: callers will persist a random token.
            return false;
        }
        
        // 使用随机 IV（每条密文独立），IV 与密文一同存储，避免固定 IV 带来的结构泄露
        if (!in_array('aes-256-gcm', openssl_get_cipher_methods(), true)) {
            return false;
        }

        $nonce = random_bytes(12);
        $tag = '';
        $encrypted = openssl_encrypt($url, 'aes-256-gcm', hash('sha256', $key, true), OPENSSL_RAW_DATA, $nonce, $tag);
        if ($encrypted === false || strlen($tag) !== 16) {
            return false;
        }
        return 'g1.' . external_link_base64url_encode($nonce . $tag . $encrypted);
    } else {
        // 随机字符串方式（默认）
        return generate_random_string(16);
    }
}

function external_link_base64url_encode($value) {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function external_link_base64url_decode($value) {
    $padding = strlen($value) % 4;
    if ($padding) {
        $value .= str_repeat('=', 4 - $padding);
    }
    return base64_decode(strtr($value, '-_', '+/'), true);
}

function external_link_decrypt_url($encrypted_key, $key) {
    if (strpos($encrypted_key, 'g1.') === 0) {
        $raw = external_link_base64url_decode(substr($encrypted_key, 3));
        if ($raw === false || strlen($raw) <= 28) {
            return false;
        }
        return openssl_decrypt(
            substr($raw, 28),
            'aes-256-gcm',
            hash('sha256', $key, true),
            OPENSSL_RAW_DATA,
            substr($raw, 0, 12),
            substr($raw, 12, 16)
        );
    }

    // Keep links generated by earlier AES-CBC versions working.
    $raw = base64_decode(str_replace(' ', '+', $encrypted_key), true);
    if ($raw === false || strlen($raw) <= 16) {
        return false;
    }
    return openssl_decrypt(substr($raw, 16), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, substr($raw, 0, 16));
}

// 生成随机字符串（用于随机字符串方式）
function generate_random_string($length = 16) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[wp_rand(0, strlen($characters) - 1)];
    }
    return $random_string . '_' . time();
}

/**
 * 跳转页 slug 清洗
 */
function external_link_sanitize_slug($slug) {
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = trim($slug, '-');
    if ($slug === '') { $slug = 'dinterception'; }
    return $slug;
}

/**
 * 获取跳转页 slug（可在设置中自定义，默认 dinterception）
 */
function external_link_get_slug() {
    $settings = get_option('dmy_link_settings');
    $slug = isset($settings['dmy_link_slug']) ? $settings['dmy_link_slug'] : 'dinterception';
    return external_link_sanitize_slug($slug);
}

/**
 * 构造跳转链接（根据自定义 slug 生成）
 */
function external_link_build_redirect_url($encrypted_key) {
    $slug = external_link_get_slug();
    return esc_url(home_url('/' . $slug . '?a=' . urlencode($encrypted_key)));
}

function external_link_normalize_url($url) {
    if (!is_string($url)) {
        return false;
    }
    $url = trim(wp_unslash($url));
    if ($url === '' || strlen($url) > 2048 || preg_match('/[\x00-\x1F\x7F]/', $url)) {
        return false;
    }
    $parts = wp_parse_url($url);
    if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
        return false;
    }
    if (!in_array(strtolower($parts['scheme']), array('http', 'https'), true) || isset($parts['user']) || isset($parts['pass'])) {
        return false;
    }
    return esc_url_raw($url, array('http', 'https'));
}

function external_link_normalize_host($host) {
    return strtolower(rtrim((string) $host, '.'));
}

function external_link_urls_share_host($left, $right) {
    $left_parts = wp_parse_url($left);
    $right_parts = wp_parse_url($right);
    return is_array($left_parts) && is_array($right_parts)
        && !empty($left_parts['host']) && !empty($right_parts['host'])
        && external_link_normalize_host($left_parts['host']) === external_link_normalize_host($right_parts['host']);
}

// 拦截所有外部链接并生成跳转Key
function external_link_intercept_links($content) {
    // 检查总开关状态
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        return $content; // 开关关闭时返回原始内容
    }

    return preg_replace_callback(
        '/<a\s+([^>]*?)href="([^"]*)"([^>]*?)>/i', 
        function($matches) use ($settings) {
            $url = $matches[2];
            $beforeHref = $matches[1];
            $afterHref = $matches[3];

            // 检查是否是内部链接或白名单链接
            if (!is_internal_link($url) && !is_whitelisted_link($url, 'dmy_link_settings')) {
                // 已标记过则跳过，避免重复叠加属性
                if (stripos($beforeHref . $afterHref, 'data-external-link') === false) {
                    $beforeHref .= ' data-external-link="1"';
                }
                return '<a ' . $beforeHref . 'href="' . $url . '"' . $afterHref . '>';
            }
            
            return '<a ' . $beforeHref . 'href="' . $url . '"' . $afterHref . '>';
        }, 
        $content
    );
}
add_filter('the_content', 'external_link_intercept_links');

// 判断是否是内部链接
function is_internal_link($url) {
    $parsed_url = wp_parse_url($url);
    $home_url = wp_parse_url(home_url());
    
    // 相对路径链接（没有host）视为内部链接
    if (!isset($parsed_url['host'])) {
        return true;
    }
    
    return isset($home_url['host'])
        && external_link_normalize_host($parsed_url['host']) === external_link_normalize_host($home_url['host']);
}

function external_link_rule_matches_url($url, $rule) {
    $parsed_url = wp_parse_url($url);
    if (!is_array($parsed_url) || empty($parsed_url['host'])) {
        return false;
    }
    $explicit_subdomains = strpos($rule, '*.') === 0;
    $has_scheme = strpos($rule, '://') !== false;
    $allow_subdomains = $explicit_subdomains || !$has_scheme;
    if ($explicit_subdomains) {
        $rule = substr($rule, 2);
    }
    $candidate = strpos($rule, '://') === false ? 'https://' . $rule : $rule;
    $parsed_rule = wp_parse_url($candidate);
    if (!is_array($parsed_rule) || empty($parsed_rule['host'])) {
        return false;
    }

    $url_host = external_link_normalize_host($parsed_url['host']);
    $allowed_host = external_link_normalize_host($parsed_rule['host']);
    $host_matches = $url_host === $allowed_host;
    if ($allow_subdomains && strlen($url_host) > strlen($allowed_host)) {
        $host_matches = substr($url_host, -(strlen($allowed_host) + 1)) === '.' . $allowed_host;
    }
    if (!$host_matches) {
        return false;
    }

    $url_path = isset($parsed_url['path']) ? $parsed_url['path'] : '/';
    $allowed_path = isset($parsed_rule['path']) ? rtrim($parsed_rule['path'], '/') : '';
    return $allowed_path === '' || $url_path === $allowed_path || strpos($url_path, $allowed_path . '/') === 0;
}

// 检查链接是否在白名单
function is_whitelisted_link($url, $option_name) {
    $options = get_option($option_name);
    if (!isset($options['dmy_link_whitelist']) || !is_string($options['dmy_link_whitelist'])) {
        return false;
    }
    $whitelist = explode("\n", trim($options['dmy_link_whitelist']));

    foreach ($whitelist as $whitelisted) {
        $whitelisted = trim($whitelisted);
        if (empty($whitelisted)) {
            continue;
        }

        if (external_link_rule_matches_url($url, $whitelisted)) {
            return true;
        }
    }

    return false;
}

//
// Referer 防护辅助函数
//
function external_is_same_site_referer($referer) {
    if (empty($referer)) {
        return false;
    }
    return external_link_urls_share_host($referer, home_url());
}

function external_is_referer_whitelisted($referer, $settings) {
    if (empty($referer)) {
        return false;
    }
    if (!isset($settings['dmy_link_referer_whitelist']) || !is_string($settings['dmy_link_referer_whitelist'])) {
        return false;
    }
    $whitelist = explode("\n", trim($settings['dmy_link_referer_whitelist']));

    foreach ($whitelist as $whitelisted) {
        $whitelisted = trim($whitelisted);
        if ($whitelisted === '') {
            continue;
        }
        if (external_link_rule_matches_url($referer, $whitelisted)) {
            return true;
        }
    }
    return false;
}

// 部分代码是不使用的老代码/在部分情况可以触发
function external_link_redirect() {
    // 检查总开关状态
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        return; // 开关关闭时不处理重定向
    }


    if (isset($_GET['a'])) {
        // Referer 防护 禁止站外直接访问跳转页
        if (!empty($settings['dmy_link_referer_protect'])) {
            $referer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : '';
            $allow_empty = !empty($settings['dmy_link_referer_allow_empty']);
            $is_safe = ($referer && (external_is_same_site_referer($referer) || external_is_referer_whitelisted($referer, $settings))) || (!$referer && $allow_empty);

            if (!$is_safe) {
                $home_url = home_url('/');
                $back_to_home_button = sprintf(
                    '<br><br><a href="%s" style="padding: 10px 20px; background-color: #0073aa; color: #fff; text-decoration: none; border-radius: 5px;">返回首页</a>',
                    esc_url($home_url)
                );
                wp_die(
                    __('危险：禁止站外直接访问跳转页面', 'external-link') . $back_to_home_button,
                    __('访问受限', 'external-link'),
                    ['response' => 403, 'back_link' => false]
                );
            }
        }

        $encrypted_key = sanitize_text_field(wp_unslash($_GET['a']));
        $link = get_transient('dmy_link_' . $encrypted_key);
        
        
        // 修复URL传输中+号被转换为空格的问题
            $encrypted_key = str_replace(' ', '+', $encrypted_key);
        // 尝试AES解密（如果是AES加密的链接）
        if (!$link) {
            $settings = get_option('dmy_link_settings');
            if (isset($settings['dmy_link_verification_method']) && 
                $settings['dmy_link_verification_method'] === 'aes_encryption' &&
                !empty($settings['dmy_link_aes_key'])) {
                
                $link = external_link_decrypt_url($encrypted_key, $settings['dmy_link_aes_key']);
            }
        }

        $link = external_link_normalize_url($link);

        if (!$link) {
            // 返回首页的按钮
            $home_url = home_url('/'); 
            $back_to_home_button = sprintf(
                '<br><br><a href="%s" style="padding: 10px 20px; background-color: #0073aa; color: #fff; text-decoration: none; border-radius: 5px;">返回首页</a>',
                esc_url($home_url)
            );

            // 显示错误信息
            wp_die(
                __('<span style="font-weight: 600; color: #d72c2cbd;">管理员:</span>拦截Token过期,你不可以在使用此Token,<span style="color: #d78d2cbd;">你可以刷新页面重新获取 </span><br> 如果刷新依旧看到这个页面请联系本站长处理', 'external-link') . $back_to_home_button,
                __('拦截Token过期提示也有可能是wordpress出现错误', 'external-link'), 
                ['response' => 404, 'back_link' => false]
            );
        }

        include_once(plugin_dir_path(__FILE__) . 'external-link-template.php');
        exit;
    }
}
add_action('init', 'external_link_redirect');


// =============================================================
// WebStack 导航主题适配
// 导航条目 URL 存放于自定义字段 _sites_link，由模板直接 echo 输出，
// 不经过 the_content 过滤器。同时该字段还被用于拼接 favicon 接口
// 地址（如 format_url($m_link_url) 取域名），因此不能改写 _sites_link。
// 采用 output buffer 方案：仅替换 HTML 中指向外链的 <a href="...">，
// 完全不影响 <img src>、favicon 拼接等其它用途。
// =============================================================

/**
 * 判断当前请求是否需要启用 WebStack 前台外链接管
 *
 * @return bool
 */
function external_webstack_should_rewrite() {
    // 总开关未启用则不做任何接管
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        return false;
    }
    // 仅 WebStack 主题且仅前台浏览时接管，避免影响后台编辑/其它上下文
    if (!is_webstack_themes() || is_admin()) {
        return false;
    }
    // 排除 REST API / 后台异步请求，避免破坏编辑与接口场景
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return false;
    }
    // 排除 AJAX 请求
    if (wp_doing_ajax()) {
        return false;
    }
    // 跳转页本身不处理，避免重复
    if (get_query_var('dinterception') == 1) {
        return false;
    }
    return true;
}

/**
 * output buffer 回调：为 HTML 中指向外链的 <a> 添加 data-external-link="1" 标记，
 * 保留原始 href（利于 SEO），由前端 JS 在点击时实时换取跳转链接。
 * 不写死跳转 URL，彻底避免 CDN / 页面缓存导致 Token 过期失效。
 *
 * @param string $buffer
 * @return string
 */
function external_webstack_buffer_rewrite_links($buffer) {
    // 不处理非 HTML 内容（JSON / 空）
    if (empty($buffer)) {
        return $buffer;
    }

    return preg_replace_callback(
        '/<a\s+([^>]*?)href="([^"]*)"([^>]*?)>/i',
        function ($m) {
            $url        = $m[2];
            $beforeHref = $m[1];
            $afterHref  = $m[3];

            // 跳过锚点、javascript、mailto 等非 http(s) 外链
            if (!preg_match('/^https?:\/\//i', $url)) {
                return $m[0];
            }
            // 站内链接或白名单直接放行
            if (is_internal_link($url) || is_whitelisted_link($url, 'dmy_link_settings')) {
                return $m[0];
            }

            // 已标记过则跳过，避免重复叠加属性
            if (stripos($beforeHref . $afterHref, 'data-external-link') !== false) {
                return $m[0];
            }

            // 追加标记：前端点击时实时换链
            $beforeHref .= ' data-external-link="1"';

            return '<a ' . $beforeHref . 'href="' . $url . '"' . $afterHref . '>';
        },
        $buffer
    );
}

/**
 * 在 WebStack 主题前台启动 output buffer，渲染完成后改写外链
 */
function external_webstack_start_buffer() {
    if (!external_webstack_should_rewrite()) {
        return;
    }
    ob_start('external_webstack_buffer_rewrite_links');
}
add_action('template_redirect', 'external_webstack_start_buffer', 20);

// 添加重定向规则
function external_link_rewrite_rules() {
    $slug = external_link_get_slug();
    $pattern = '^' . preg_quote($slug, '/') . '/?$';
    add_rewrite_rule($pattern, 'index.php?dinterception=1', 'top');
}
add_action('init', 'external_link_rewrite_rules');

register_activation_hook(__FILE__, 'external_link_activate');
function external_link_activate() {
    // 激活时按照当前设置生成重写规则并刷新
    external_link_rewrite_rules();
    flush_rewrite_rules();
}

add_action('update_option_dmy_link_settings', 'external_link_maybe_flush_on_slug_change', 10, 3);
function external_link_maybe_flush_on_slug_change($old_value, $value, $option) {
    // 设置保存时，若 slug 发生变化则刷新固定链接
    $old_slug = isset($old_value['dmy_link_slug']) ? external_link_sanitize_slug($old_value['dmy_link_slug']) : 'dinterception';
    $new_slug = isset($value['dmy_link_slug']) ? external_link_sanitize_slug($value['dmy_link_slug']) : 'dinterception';
    if ($old_slug !== $new_slug) {
        external_link_rewrite_rules();
        flush_rewrite_rules();
    }
}

// 添加查询变量
function external_link_query_vars($vars) {
    $vars[] = 'dinterception';
    return $vars;
}
add_filter('query_vars', 'external_link_query_vars');

// 处理重定向逻辑
function external_link_template_redirect() {
    if (get_query_var('dinterception') == 1) {
        external_link_redirect();
    }
}
add_action('template_redirect', 'external_link_template_redirect');
// 注册WordPress原生AJAX处理
add_action('wp_ajax_external_link_convert', 'external_link_ajax_convert');
add_action('wp_ajax_nopriv_external_link_convert', 'external_link_ajax_convert');

function external_link_ajax_convert() {
    // 此接口为公开URL加密服务（非状态修改操作），同时注册了 nopriv
    // Nginx 缓存环境下 PHP 输出的 nonce 会过期，因此不使用 check_ajax_referer
    // 改用 Referer 检查防止外部站点滥用
    $referer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : '';
    if ($referer && !external_is_same_site_referer($referer)) {
        wp_send_json_error(array('message' => '非法请求'), 403);
    }

    // 检查总开关状态
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        wp_send_json_error(array('message' => '插件已关闭'));
    }

    $url = isset($_POST['url']) ? external_link_normalize_url($_POST['url']) : false;
    if (!$url) {
        wp_send_json_error(array('message' => 'URL 无效'), 400);
    }

    // 站内或白名单直接放行
    if (is_internal_link($url) || is_whitelisted_link($url, 'dmy_link_settings')) {
        wp_send_json_success(array('url' => $url));
    }

    // 使用统一加密函数
    $encrypted_key = external_link_encrypt_url($url);
    $settings = get_option('dmy_link_settings');
    
    // 根据加密方式设置过期时间
    $method = isset($settings['dmy_link_verification_method']) ? $settings['dmy_link_verification_method'] : 'random_string';
    
    if ($method === 'random_string' || $encrypted_key === false) {
        if ($encrypted_key === false) {
            $encrypted_key = generate_random_string(16);
        }
        $ttl = isset($settings['dmy_link_expiration']) ? (int)$settings['dmy_link_expiration'] : 5;
        $ttl = max(1, min(1440, $ttl));
        $cache_key = 'dmy_link_url_' . hash('sha256', $url);
        $cached_key = get_transient($cache_key);
        if ($cached_key && get_transient('dmy_link_' . $cached_key) === $url) {
            wp_send_json_success(array('url' => external_link_build_redirect_url($cached_key)));
        }
        set_transient('dmy_link_' . $encrypted_key, $url, $ttl * MINUTE_IN_SECONDS);
        set_transient($cache_key, $encrypted_key, $ttl * MINUTE_IN_SECONDS);
    } else {
        // AES方式设较长但有限的过期时间（避免 options 表无限膨胀）
        // AES-GCM is self-contained and does not need a database record.
    }

    wp_send_json_success(array('url' => external_link_build_redirect_url($encrypted_key)));
}


// 根据设置条件加载圈子或社区功能脚本
add_action( 'wp_enqueue_scripts', function () {
    // 检查总开关状态
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        return; // 开关关闭时不加载脚本
    }

    // 检查启用的功能类型
    $enabled_type = '';
    $selector = '';
    
    if (isset($settings['dmy_link_function_type'])) {
        $enabled_type = $settings['dmy_link_function_type'];
        
        if ($enabled_type === 'circle') {
            $selector = isset($settings['dmy_link_circle_selector']) && !empty($settings['dmy_link_circle_selector']) 
                ? $settings['dmy_link_circle_selector'] 
                : '.topic-content';
        } elseif ($enabled_type === 'forums') {
            $selector = isset($settings['dmy_link_forums_selector']) && !empty($settings['dmy_link_forums_selector']) 
                ? $settings['dmy_link_forums_selector'] 
                : '.forum-article';
        }
    }
    
    // 如果启用了任一功能，则加载脚本
    if (false && !empty($enabled_type) && !empty($selector)) {
        wp_enqueue_script(
            'external-link-circle',
            plugin_dir_url( __FILE__ ) . 'js/external-link-circle.js',
            array(),
            filemtime( plugin_dir_path( __FILE__ ) . 'js/external-link-circle.js' ),
            true
        );
        
        // 传递选择器设置到JavaScript
        wp_localize_script('external-link-circle', 'external_link_circle_config', array(
            'selector' => $selector,
            'ajax_url' => admin_url('admin-ajax.php'),
            'function_type' => $enabled_type
        ));
    }
} );

// 插件卸载时清理数据
function dmy_link_uninstall() {
    // 删除插件设置选项
    delete_option('dmy_link_settings');

    // 清理所有插件相关的瞬态数据（本站点 + 多站点）
    global $wpdb;
    $transients = $wpdb->get_col(
        "SELECT option_name FROM $wpdb->options
        WHERE option_name LIKE '_transient_dmy_link_%'
        OR option_name LIKE '_transient_timeout_dmy_link_%'"
    );

    foreach ($transients as $transient) {
        $name = str_replace(array('_transient_', '_site_transient_'), '', $transient);
        delete_transient($name);
        delete_site_transient($name);
    }

    // 移除跳转页重写规则
    flush_rewrite_rules();
}
register_uninstall_hook(__FILE__, 'dmy_link_uninstall');


// 适配子比主题：接管评论链接和用户中心重定向
// 必须在 after_setup_theme 中执行，此时主题及自定义函数均已注册完毕
if (is_zibll_themes()) {
    add_action('after_setup_theme', 'external_link_override_zibll_filters', 99);
}

/**
 * 在主题加载完毕后，移除主题原版及自定义版的评论/用户模态框处理器，替换为插件版
 * 同时强制关闭子比主题的外链重定向功能，避免与插件冲突
 */
function external_link_override_zibll_filters() {
    // 强制关闭子比主题的外链重定向和外链重定向鉴权
    // _pz() 使用静态缓存无法从外部重置，因此同时用 _spz() 写入 option 确保下次请求生效
    if (_pz('go_link_s')) {
        _spz('go_link_s', false);
    }
    if (_pz('go_link_nonce_s')) {
        _spz('go_link_nonce_s', false);
    }

    // 移除主题原版评论链接处理
    remove_filter('get_comment_author_link', 'add_redirect_comment_link', 5);
    remove_filter('comment_text', 'add_redirect_comment_link', 99);
    // 移除主题自定义版（zidingyi 中可能注册的）
    remove_filter('get_comment_author_link', 'wxs_add_redirect_comment_link', 5);
    remove_filter('comment_text', 'wxs_add_redirect_comment_link', 99);

    // 移除子比主题 the_content 中的外链处理
    remove_filter('the_content', 'the_content_nofollow', 999);
    // 移除自定义版 the_content 外链处理
    if (function_exists('wxs_the_content_nofollow')) {
        remove_filter('the_content', 'wxs_the_content_nofollow', 999);
    }

    // 注册插件版评论链接处理
    add_filter('get_comment_author_link', 'external_add_redirect_comment_link', 6);
    add_filter('comment_text', 'external_add_redirect_comment_link', 100);

    // 移除主题原版用户详情模态框
    remove_action('wp_ajax_user_details_data_modal', 'zib_ajax_user_details_data_modal');
    remove_action('wp_ajax_nopriv_user_details_data_modal', 'zib_ajax_user_details_data_modal');
    // 移除主题自定义版用户详情模态框
    remove_action('wp_ajax_user_details_data_modal', 'wxs_zib_ajax_user_details_data_modal');
    remove_action('wp_ajax_nopriv_user_details_data_modal', 'wxs_zib_ajax_user_details_data_modal');

    // 注册插件版用户详情模态框
    add_action('wp_ajax_user_details_data_modal', 'external_zib_ajax_user_details_data_modal');
    add_action('wp_ajax_nopriv_user_details_data_modal', 'external_zib_ajax_user_details_data_modal');
}


/**
 * 插件的评论链接处理函数（替换主题的add_redirect_comment_link）
 */
function external_add_redirect_comment_link($text = '') {
    $settings = get_option('dmy_link_settings');
    // 若插件总开关关闭，直接返回原始内容
    if (empty($settings['dmy_link_enable'])) {
        return $text;
    }
    // 处理评论内容中的<a>标签链接
    return external_go_link($text);
}

/**
 * 插件的链接处理逻辑（替代主题的go_link）
 * @param string $text 链接文本或含<a>标签的HTML
 * @param bool $link 为true时视为纯URL，直接返回跳转后的URL
 */
function external_go_link($text = '', $link = false) {
    $settings = get_option('dmy_link_settings');
    if (empty($settings['dmy_link_enable'])) {
        return $text;
    }

    // 纯链接模式：直接返回跳转URL或原URL
    if ($link) {
        if (!is_internal_link($text) && !is_whitelisted_link($text, 'dmy_link_settings')) {
            return external_get_redirect_url($text);
        }
        return $text;
    }

    // 1. 处理纯链接（如评论作者链接，可能直接是URL而非<a>标签）
    if (preg_match('/^https?:\/\//', $text) && !preg_match('/<a.*?>/', $text)) {
        if (!is_internal_link($text) && !is_whitelisted_link($text, 'dmy_link_settings')) {
            return external_get_redirect_url($text);
        }
        return $text;
    }

    // 2. 处理带<a>标签的链接（如评论内容中的链接）
    preg_match_all("/<a(.*?)href=['\"](.*?)['\"](.*?)>/", $text, $matches);
    if ($matches) {
        foreach ($matches[2] as $val) {
            if (!is_internal_link($val) && !is_whitelisted_link($val, 'dmy_link_settings')) {
                $redirect_url = external_get_redirect_url($val);
                $text = str_replace(
                    array("href=\"$val\"", "href='$val'"),
                    "href=\"$redirect_url\"",
                    $text
                );
            }
        }
        // 统一添加target="_blank"（避免重复添加）
        foreach ($matches[0] as $a_tag) {
            if (!preg_match('/target=["\']_blank["\']/', $a_tag)) {
                $text = str_replace($a_tag, str_replace('<a', '<a target="_blank"', $a_tag), $text);
            }
        }
    }
    return $text;
}

/**
 * 生成插件的跳转链接（替代主题的zib_get_gourl）
 */
function external_get_redirect_url($url) {
    $url = external_link_normalize_url($url);
    if (!$url) {
        return '';
    }
    $encrypted_key = external_link_encrypt_url($url);
    // 存储链接（复用插件原有逻辑）
    $settings = get_option('dmy_link_settings');
    $method = isset($settings['dmy_link_verification_method']) ? $settings['dmy_link_verification_method'] : 'random_string';
    if ($method === 'random_string' || $encrypted_key === false) {
        $cache_key = 'dmy_link_url_' . hash('sha256', $url);
        $cached_key = get_transient($cache_key);
        if ($cached_key && get_transient('dmy_link_' . $cached_key) === $url) {
            return external_link_build_redirect_url($cached_key);
        }
        $encrypted_key = generate_random_string(16);
        $expiration = isset($settings['dmy_link_expiration']) ? intval($settings['dmy_link_expiration']) : 5;
        $expiration = max(1, min(1440, $expiration));
        set_transient('dmy_link_' . $encrypted_key, $url, $expiration * MINUTE_IN_SECONDS);
        set_transient($cache_key, $encrypted_key, $expiration * MINUTE_IN_SECONDS);
    } else {
        // AES-GCM tokens contain the authenticated URL and need no transient.
    }
    return external_link_build_redirect_url($encrypted_key);
}


//查看用户全部详细资料的模态框
function external_zib_ajax_user_details_data_modal()
{
    $user_id = !empty($_REQUEST['id']) ? absint(wp_unslash($_REQUEST['id'])) : 0;

    $user = get_userdata($user_id);
    if (!$user_id || empty($user->ID)) {
        zib_ajax_notice_modal('danger', __('用户不存在或参数传入错误', 'zib_language'));
    }

    echo external_zib_get_user_details_data_modal($user_id);
    exit();
}


//获取用户详细资料
function external_zib_get_user_details_data_modal($user_id = '', $class = 'mb10 flex', $t_class = 'muted-2-color', $v_class = '')
{
    if (!$user_id) {
        return;
    }

    $current_id = get_current_user_id();
    $udata      = get_userdata($user_id);
    if (!$udata) {
        return;
    }

    $privacy = zib_get_user_meta($user_id, 'privacy', true);

    $datas = array(
        array(
            'title'   => __('签名', 'zib_language'),
            'value'   => get_user_desc($user_id, false),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => false,
        ),
        array(
            'title'   => __('注册时间', 'zib_language'),
            'value'   => get_date_from_gmt($udata->user_registered),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => false,
        ), array(
            'title'   => __('最后登录', 'zib_language'),
            'value'   => get_user_meta($user_id, 'last_login', true),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => false,
        ), array(
            'title'   => __('邮箱', 'zib_language'),
            'value'   => esc_attr($udata->user_email),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => true,
        ), array(
            'title'   => __('性别', 'zib_language'),
            'value'   => esc_attr(get_user_meta($user_id, 'gender', true)),
            'spare'   => __('保密', 'zib_language'),
            'no_show' => true,
        ), array(
            'title'   => __('地址', 'zib_language'),
            'value'   => esc_textarea(zib_get_user_meta($user_id, 'address', true)),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => true,
        ), array(
            'title'   => __('个人网站', 'zib_language'),
            'value'   => external_zib_get_url_link($user_id), //修改
            'spare'   => __('未知', 'zib_language'),
            'no_show' => true,
        ), array(
            'title'   => 'QQ',
            'value'   => esc_attr(zib_get_user_meta($user_id, 'qq', true)),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => true,
        ), array(
            'title'   => __('微信', 'zib_language'),
            'value'   => esc_attr(zib_get_user_meta($user_id, 'weixin', true)),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => true,
        ), array(
            'title'   => __('微博', 'zib_language'),
            'value'   => esc_url(zib_get_user_meta($user_id, 'weibo', true)),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => true,
        ), array(
            'title'   => 'Github',
            'value'   => esc_url(zib_get_user_meta($user_id, 'github', true)),
            'spare'   => __('未知', 'zib_language'),
            'no_show' => true,
        ),
    );

    $lists = '';

    //用户认证
    if (_pz('user_auth_s', true)) {
        $auth_name = zib_get_user_auth_info_link($user_id, 'c-blue');
        $auth_name = $auth_name ? $auth_name : __('未认证', 'zib_language');
        $lists .= '<div class="' . $class . '" style="min-width: 50%;">';
        $lists .= '<div class="author-set-left ' . $t_class . '" style="min-width: 80px;">' . __('认证', 'zib_language') . '</div>';
        $lists .= '<div class="author-set-right mt6' . $v_class . '">' . $auth_name . '</div>';
        $lists .= '</div>';
    }

    //用户徽章
    if (_pz('user_medal_s', true)) {
        $user_medal = zib_get_user_medal_show_link($user_id, '', 5);
        $user_medal = $user_medal ? $user_medal : __('暂无徽章', 'zib_language');

        $lists .= '<div class="' . $class . '" style="min-width: 50%;">';
        $lists .= '<div class="author-set-left ' . $t_class . '" style="min-width: 80px;">' . __('徽章', 'zib_language') . '</div>';
        $lists .= '<div class="author-set-right mt6' . $v_class . '">' . $user_medal . '</div>';
        $lists .= '</div>';
    }

    foreach ($datas as $data) {
        if (!is_super_admin() && $data['no_show'] && 'public' != $privacy && $current_id != $user_id) {
            if (('just_logged' == $privacy && !$current_id) || 'just_logged' != $privacy) {
                $data['value'] = __('用户未公开', 'zib_language');
            }
        }
        $lists .= '<div class="' . $class . '" style="min-width: 50%;">';
        $lists .= '<div class="author-set-left ' . $t_class . '" style="min-width: 80px;">' . $data['title'] . '</div>';
        $lists .= '<div class="author-set-right mt6' . $v_class . '">' . ($data['value'] ? $data['value'] : $data['spare']) . '</div>';
        $lists .= '</div>';
    }

    $header = '<div class="mb10 border-bottom touch" style="padding-bottom: 12px;">';
    $header .= '<button class="close ml10" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button>';
    $header .= '<div class="" style="">';
    $header .= zib_get_post_user_box($user_id);
    $header .= '</div>';
    $header .= '</div>';

    $html = '<div class="mini-scrollbar scroll-y max-vh5 flex hh">' . $lists . '</div>';
    return $header . $html;
}


function external_zib_get_url_link($user_id, $class = 'focus-color')
{
    $user_url = get_userdata($user_id)->user_url;
    $url_name = zib_get_user_meta($user_id, 'url_name', true) ?: $user_url;
    $user_url = external_go_link($user_url, true);
    return $user_url ? '<a class="' . $class . '" href="' . esc_url($user_url) . '" target="_blank">' . esc_attr($url_name) . '</a>' : 0;
}
