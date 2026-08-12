<!-- 腾讯风格 -->
<div class="external-link-tencent">
    <div class="external-link-tencent-box">
        <!-- logo -->
        <div class="external-link-tencent-logo">
            <img src="<?php echo esc_url($logourl); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>logo">
        </div>
        <!-- 内容 -->
        <div class="external-link-tencent-title">
            <div class="external-link-tencent-title-div">
                <div class="external-link-tencent-title-icon">
                    您即将离开
                    <?php echo get_bloginfo('name'); ?>，请注意您的账号财产安全
                </div>
                <div class="external-link-tencent-titlelink">
                    <a>
                        <?php echo esc_url($link); ?>
                    </a>
                </div>
            </div>
            <div class="external-link-tencent-link-a">
                <a href="<?php echo esc_url($link); ?>" target="_self">继续访问</a>
            </div>
        </div>
    </div>
</div>
