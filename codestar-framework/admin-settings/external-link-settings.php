<?php
/**
 * 外链跳转插件 - CSF设置面板配置
 * 
 * @package 外链跳转插件
 * @author  木木
 * @version 1.0.0
 */

// 防止直接访问
if (!defined('ABSPATH')) exit;

/**
 * 初始化CSF设置面板
 */
function external_link_settings() {
    
    // 只有后台才执行此代码
    if (!is_admin()) {
        return;
    }
    
    // 检查CSF是否可用
    if (!class_exists('CSF')) {
        return false;
    }
    
    $prefix = 'dmy_link_settings';
    $version = external_link_plugin_version();
    
    // 创建设置页面
    CSF::createOptions($prefix, [
        'menu_title'      => '外链跳转插件',
        'menu_slug'       => $prefix,
        'menu_type'       => 'menu',
        'menu_icon'       => 'dashicons-admin-links',
        'menu_position'   => 59,
        'framework_title' => '外链跳转插件 <small style="color: #fff;">v'.$version.'</small>',
        'footer_text'     => '<style>html body .csf-theme-light .csf-header-inner::before { content: "EL" !important; }</style>版本:V'.$version,
        'show_bar_menu'   => false,
        'theme'           => is_zibll_themes() ? 'light' : 'dark',
        'show_in_customizer' => false,
        'footer_credit'   => '<i class="fa fa-fw fa-heart-o" aria-hidden="true"></i> 感谢您使用外链跳转插件',
    ]);

    // 添加各个设置面板
    external_link_create_basic_section($prefix);
    external_link_create_whitelist_section($prefix);
    external_link_create_style_section($prefix);
    external_link_create_community_section($prefix);
    external_link_create_logo_section($prefix);
    external_link_create_security_section($prefix);

    return true;
}

/**
 * 创建基本设置面板
 */
function external_link_create_basic_section($prefix) {
    $fields = [];

    // 子比主题环境下显示冲突提示
    if (is_zibll_themes()) {
        $go_link_s = _pz('go_link_s');
        $go_link_nonce_s = _pz('go_link_nonce_s');
        $needs_fix = !empty($go_link_s) || !empty($go_link_nonce_s);

        $notice_type = $needs_fix ? 'warning' : 'info';
        $notice_content = '<strong>检测到子比主题环境</strong><br>';
        if ($needs_fix) {
            $notice_content .= '<span style="color:#d63638;">子比主题的「外链重定向」或「外链重定向鉴权」已开启，会与插件冲突导致外链无法正确跳转。插件已在运行时自动接管，但建议前往 <a href="' . esc_url(admin_url('admin.php?page=zibll_options#外链重定向')) . '">子比主题设置</a> 关闭以下选项：</span>';
            if (!empty($go_link_s)) {
                $notice_content .= '<br>• <strong>外链重定向</strong>（go_link_s）— 当前：开启 → 建议关闭';
            }
            if (!empty($go_link_nonce_s)) {
                $notice_content .= '<br>• <strong>外链重定向鉴权</strong>（go_link_nonce_s）— 当前：开启 → 建议关闭';
            }
        } else {
            $notice_content .= '<span style="color:#0073aa;">子比主题的「外链重定向」和「外链重定向鉴权」均已关闭，插件可正常工作。</span>';
        }

        $fields[] = [
            'type'    => 'notice',
            'style'   => $notice_type,
            'content' => $notice_content,
        ];
    }

    $fields[] = [
        'id'      => 'dmy_link_enable',
        'type'    => 'switcher',
        'title'   => '启用插件功能',
        'label'   => '关闭后插件所有功能将停止工作',
        'default' => true,
    ];
    $fields[] = [
        'id'      => 'dmy_link_slug',
        'type'    => 'text',
        'title'   => '跳转页路径（Slug）',
        'desc'    => '用于生成跳转页地址，例如 /dinterception；只允许小写字母、数字和短横线。修改后保存设置会自动刷新固定链接。',
        'default' => 'dinterception',
        'sanitize' => 'external_link_sanitize_slug',
    ];

    CSF::createSection($prefix, [
        'title'  => '基本设置',
        'icon'   => 'fa fa-cog',
        'fields' => $fields,
    ]);
}

/**
 * 创建白名单设置面板
 */
function external_link_create_whitelist_section($prefix) {
    CSF::createSection($prefix, [
        'title'  => '白名单设置',
        'icon'   => 'fa fa-id-card-o',
        'fields' => [
            [
                'id'    => 'dmy_link_whitelist',
                'type'  => 'textarea',
                'attributes'  => array(
                    'rows' => 5,
                ),
                'title' => '白名单链接',
                'desc'  => 'wordpress设置的地址默认为白名单，每行一个链接，不需要加http://或者https://',
                'default' => '',
            ],
        ],
    ]);
}

/**
 * 创建样式设置面板
 */
function external_link_create_style_section($prefix) {
    $plugin_url = plugin_dir_url(dirname(__DIR__));
    
    CSF::createSection($prefix, [
        'title'  => '样式设置',
        'icon'   => 'fa fa-paint-brush',
        'fields' => [
            [
                'type' => 'content',
                'content' => '<style>
                    .csf--image-group{display:flex;flex-wrap:wrap;gap:24px;padding:8px 2px}
                    .csf--image-group .csf--image{margin:0;position:relative}
                    .csf--image-group .csf--image figure{width:160px;margin:0;cursor:pointer;transition:transform .2s ease,box-shadow .2s ease}
                    .csf--image-group .csf--image figure:hover{transform:translateY(-4px)}
                    .csf--image-group .csf--image img{width:100%;height:auto;border:1px solid #e8e8e8;border-radius:12px;display:block;box-shadow:0 2px 8px rgba(0,0,0,.06);transition:border-color .2s ease,box-shadow .2s ease}
                    .csf--image-group .csf--image:hover img{box-shadow:0 8px 22px rgba(0,0,0,.12)}
                    .csf--image-group .csf--image figcaption{margin-top:10px;font-size:13px;color:#555;text-align:center;line-height:1.4;font-weight:500}
                    .csf--image-group .csf--image input{display:none}
                    /* 隐藏 CSF 默认左上角黑色圆形选中标记（保留我们自定义的右上角对勾） */
                    /* image_select 渲染结构为 .csf--sibling.csf--image > figure，对勾通过 figure::before 生成 */
                    .csf--image-group .csf--image label::before,
                    .csf--image-group .csf--image label::after,
                    .csf--image-group .csf--image > label .csf--icon,
                    .csf--image-group .csf--image > label i,
                    .csf--image-group .csf--image > label span,
                    .csf--image-group .csf--image .csf--switcher,
                    .csf--image-group .csf--image .csf-check,
                    .csf--image-group .csf--image label > .csf--icon,
                    .csf--image-group .csf--image label > em,
                    .csf--image-group .csf--image label > b,
                    .csf--image-group .csf--image figure::before {display:none !important}
                    .csf--image-group .csf--image.csf--active,
                    .csf--image-group .csf--image.csf--active figure,
                    .csf--image-group .csf--image.csf--active img,
                    .csf--image-group .csf--image.csf--active figcaption {
                        outline:none !important;
                        border:none !important;
                        box-shadow:none !important;
                        background:transparent !important;
                    }
                    .csf--image-group .csf--image.csf--active img,
                    .csf--image-group .csf--image.csf--active figure img {
                        border:1px solid #ffd6e4 !important;
                    }
                    .csf--image-group .csf--image.csf--active figure::before {
                        content:"";
                        position:absolute;
                        inset:-4px;
                        border:2px solid #ff6b9d;
                        border-radius:16px;
                        pointer-events:none;
                        z-index:1;
                        box-shadow:0 10px 28px rgba(255,107,157,.22);
                    }
                    .csf--image-group .csf--image.csf--active figure::after{
                        content:"";
                        position:absolute;
                        top:-8px;
                        right:-8px;
                        width:24px;
                        height:24px;
                        background:#ff6b9d url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 16 16\'%3E%3Cpath fill=\'%23fff\' d=\'M6.7 11.3L3.4 8l1.4-1.4 1.9 1.9 4.5-4.5L12.6 5.4z\'/%3E%3C/svg%3E") center/14px no-repeat;
                        border-radius:50%;
                        border:2px solid #fff;
                        box-shadow:0 2px 8px rgba(0,0,0,.2);
                        z-index:3;
                    }
                    .csf--image-group .csf--image.csf--active img{border:1px solid #ffd6e4;box-shadow:0 12px 30px rgba(255,107,157,.18)}
                    .csf--image-group .csf--image.csf--active figcaption{color:#ff6b9d;font-weight:600}
                </style>
                <script>
                (function(){
                    window.addEventListener("load",function(){
                        var map = {
                            "external-link-default":"默认样式(茉莉小栈)",
                            "external-link-bilibili":"哔哩哔哩", 
                            "external-link-tencent":"腾讯云社区",
                            "external-link-csdn":"CSDN",
                            "external-link-zhihu":"知乎",
                            "external-link-jump":"通用跳转",
                            "external-link-moxing":"墨星博客",
                            "external-link-tiktok":"TikTok"
                        };
                        document.querySelectorAll(".csf--image-group .csf--image figure").forEach(function(fig){
                            var input = fig.querySelector("input");
                            if(!input){return}
                            var key = input.value;
                            var label = map[key] || key;
                            if(!fig.querySelector("figcaption")){
                                var cap = document.createElement("figcaption");
                                cap.textContent = label;
                                fig.appendChild(cap);
                            }
                        });
                    });
                })();
                </script>'
            ],
            [
                'id'      => 'dmy_link_style',
                'type'    => 'image_select',
                'title'   => '提示页面样式',
                'desc'    => '上方切换样式（点击图片进行选择），下方显示对应的预览图',
                'options' => [
                    'external-link-default'  => $plugin_url . 'assets/img/default-min.png',
                    'external-link-bilibili' => $plugin_url . 'assets/img/bilibili-min.png',
                    'external-link-tencent'  => $plugin_url . 'assets/img/tencent-min.png',
                    'external-link-csdn'     => $plugin_url . 'assets/img/csdn-min.png',
                    'external-link-zhihu'    => $plugin_url . 'assets/img/zhihu-min.png',
                    'external-link-jump'     => $plugin_url . 'assets/img/jump-min.png',
                    'external-link-moxing'   => $plugin_url . 'assets/img/moxingbk-min.png',
                    'external-link-tiktok'   => $plugin_url . 'assets/img/tiktok-min.png',
                ],
                'default' => 'external-link-default',
                'inline'  => true
            ]
        ],
    ]);
}

/**
 * 创建主题社区功能设置面板
 */
function external_link_create_community_section($prefix) {
    CSF::createSection($prefix, [
        'title'  => '主题社区功能',
        'icon'   => 'fa fa-comments',
        'fields' => [
            [
                'id'      => 'dmy_link_function_type',
                'type'    => 'radio',
                'title'   => '选择社区功能类型',
                'desc'    => '选择您要启用的社区功能类型，只能选择一项',
                'options' => [
                    'none'   => '不启用任何社区功能',
                    'circle' => '7b2主题圈子功能',
                    'forums' => '子比主题社区帖子功能'
                ],
                'default' => 'none',
                'inline'  => true
            ],
            [
                'id'        => 'dmy_link_circle_selector',
                'type'      => 'text',
                'title'     => '圈子内容选择器',
                'desc'      => '用于识别圈子内容的CSS选择器，默认为.topic-content<br/>如果您的主题结构不同，可以修改此选择器',
                'default'   => '.topic-content',
                'dependency' => ['dmy_link_function_type', '==', 'circle'],
            ],
            [
                'id'        => 'dmy_link_forums_selector',
                'type'      => 'text',
                'title'     => '社区帖子选择器',
                'desc'      => '用于识别社区帖子内容的CSS选择器，默认为.forum-article<br/>如果您的主题结构不同，可以修改此选择器',
                'default'   => '.forum-article',
                'dependency' => ['dmy_link_function_type', '==', 'forums'],
            ],
        ],
    ]);
}

/**
 * 创建Logo设置面板
 */
function external_link_create_logo_section($prefix) {
    CSF::createSection($prefix, [
        'title'  => 'Logo 设置',
        'icon'   => 'fa fa-image',
        'fields' => [
            [
                'id'    => 'dmy_link_logo',
                'type'  => 'upload',
                'title' => 'Logo 图片',
                'desc'  => '上传一个图片作为 logo,如果您不设置，插件并不会自动获取您网站的logo',
                'default' => '',
            ],
        ],
    ]);
}

/**
 * 创建安全设置面板
 */
function external_link_create_security_section($prefix) {
    CSF::createSection($prefix, [
        'title'  => '安全设置',
        'icon'   => 'fa fa-lock',
        'fields' => [
            [
                'id'      => 'dmy_link_verification_method',
                'type'    => 'radio',
                'title'   => '链接验证方式',
                'options' => [
                    'random_string'  => '随机字符串 + 过期机制',
                    'aes_encryption' => 'AES加密 + 后端验证',
                ],
                'default' => 'random_string',
                'desc'    => '选择链接验证的安全机制'
            ],
            [
                'id'        => 'dmy_link_expiration',
                'type'      => 'number',
                'title'     => '过期时间（分钟）',
                'desc'      => '设置外链跳转链接的过期时间，单位为分钟<br/>默认为5分钟',
                'default'   => 5,
                'min'       => 1,
                'max'       => 1440,
                'dependency' => ['dmy_link_verification_method', '==', 'random_string'],
            ],
            [
                'id'        => 'dmy_link_aes_key',
                'type'      => 'text',
                'title'     => 'AES加密密钥',
                'desc'      => '请输入32个字符的密钥（用于加密跳转链接）',
                'default'   => bin2hex(openssl_random_pseudo_bytes(16)),
                'dependency' => ['dmy_link_verification_method', '==', 'aes_encryption'],
            ],
            [
                'id'      => 'dmy_link_referer_protect',
                'type'    => 'switcher',
                'title'   => '启用 Referer 防护',
                'desc'    => '开启后，禁止非本站 Referer 直接访问跳转页（例如 /dinterception 或自定义）',
                'default' => false,
            ],
            [
                'id'        => 'dmy_link_referer_allow_empty',
                'type'      => 'switcher',
                'title'     => '允许空 Referer',
                'desc'      => '某些浏览器/场景可能不发送 Referer，可选择放行空 Referer',
                'default'   => true,
                'dependency' => ['dmy_link_referer_protect', '==', 'true'],
            ],
            [
                'id'        => 'dmy_link_referer_whitelist',
                'type'      => 'textarea',
                'title'     => 'Referer 白名单（可选）',
                'desc'      => '每行一个域名或URL（例如 example.com 或 https://sub.example.com）。在启用 Referer 防护时，允许这些来源访问跳转页。',
                'default'   => '',
                'dependency' => ['dmy_link_referer_protect', '==', 'true'],
            ],
        ],
    ]);
}

