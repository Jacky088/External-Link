<!-- csdn风格 -->
<div class="external-link-csdn">
    <div class="external-link-csdn-box">
        <!-- logo -->
        <div class="external-link-csdn-logo">
            <img src="<?php echo esc_url($logourl); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>logo">
        </div>
        <!-- 内容 -->
        <div class="external-link-csdn-title">
            <div class="external-link-csdn-title-div">
                <div class="external-link-csdn-title-icon">
                    <img class="loading-img"
                        src="<?php echo EXTERNAL_LINK_URL . '/assets/img/external-link-csdn.png'; ?> "
                        alt="<?php echo get_bloginfo('name'); ?>-提示警告">
                    <div class="external-link-csdn-title-text">请注意您的账号和财产安全</div>
                </div>
                <div class="external-link-csdn-titlelink">
                    <span>您即将离开
                        <?php echo get_bloginfo('name'); ?>，去往：
                    </span>
                    <a>
                        <?php echo esc_url($link); ?>
                    </a>
                </div>
            </div>
            <div class="external-link-csdn-link-a">
                <a href="<?php echo esc_url($link); ?>" target="_self">继续</a>
            </div>
        </div>
    </div>
</div>
