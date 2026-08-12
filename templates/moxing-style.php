<div class="external-link-moxing-box">
  <div class="external-link-moxing-logo">
   <img src="<?php echo esc_url($logourl); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>logo">
  </div>

  <p class="external-link-moxing-title">
    <span class="external-link-moxing-title-icon">🔗</span>
    <span class="external-link-moxing-title-text">
      <?php echo esc_url($link); ?>
    </span>
  </p>

  <div class="external-link-moxing-link-a">
    <a href="<?php echo esc_url($link); ?>" class="external-link-moxing-link-background-pink">
      继续前往
    </a>
    <a href="<?php echo home_url(); ?>" class="external-link-moxing-link-background-blue">
      回到主页
    </a>
  </div>
</div>