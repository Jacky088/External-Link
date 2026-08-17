<!-- bilibili风格 -->
<div class="external-link-bilibili">
    <div class="external-link-bilibili-box">
        <!-- 内容 -->
        <div class="external-link-bilibili-title">
            <div class="external-link-bilibili-title-div-title-no2">
                <div class="external-link-bilibili-title-icon"> 
                    <img class="loading-img"
                    src="<?php echo esc_url(EXTERNAL_LINK_URL . 'assets/img/external-link-bilibili.png'); ?>"
                        alt="">
                    <div class="external-link-bilibili-title-text">即将离开
                        <?php echo esc_html(get_bloginfo('name')); ?>，请保护好个人信息
                    </div>
                </div>
                <div class="external-link-bilibili-title-div">
                    <div class="external-link-csdn-title-icon">
                        <img class="loading-img"
                            src="<?php echo esc_url(EXTERNAL_LINK_URL . 'assets/img/external-link-bilibili-link.png'); ?>"
                            alt="<?php echo esc_attr(get_bloginfo('name')); ?>-提示警告">
                        <span>
                            <?php echo esc_url($link); ?>
                        </span>
                    </div>
                </div>
                <div class="external-link-bilibili-link-a">
                    <a class="external-link-bilibili-link-a-no1" href="<?php echo esc_url(home_url('/')); ?>">返回文章</a>
                    <a class="external-link-bilibili-link-a-no2" href="<?php echo esc_url($link); ?>" target="_self">继续访问</a>
                </div>
            </div>
        </div>
    </div>
</div>
