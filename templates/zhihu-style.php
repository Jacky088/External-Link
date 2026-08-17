<!-- 知乎风格 -->
<div class="external-link-zhihu">
    <div class="external-link-zhihu-box">
        <!-- logo -->
        <div class="external-link-zhihu-logo">
            <img src="<?php echo esc_url($logourl); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>logo">
        </div>
        <!-- 内容 -->
        <div class="external-link-zhihu-title">
            <div class="external-link-zhihu-title-div">
                <div class="external-link-zhihu-title-icon">
                    <div class="external-link-zhihu-title-text">即将离开
                        <?php echo esc_html(get_bloginfo('name')); ?>
                    </div>
                    <p>您即将离开
                        <?php echo esc_html(get_bloginfo('name')); ?>，请注意您的帐号和财产安全。
                    </p>
                    <p class="external-link-zhihu-titlelink-p-no2">
                        <?php echo esc_url($link); ?>
                    </p>
                </div>
                <div class="external-link-zhihu-link-a">
                    <a href="<?php echo esc_url($link); ?>" target="_self">继续访问</a>
                </div>
            </div>
        </div>
    </div>
</div>
