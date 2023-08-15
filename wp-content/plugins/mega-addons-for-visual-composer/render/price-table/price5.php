<div class="<?php echo esc_attr($style); ?>  wow bounce" data-wow-duration="1's" style="visibility: visible; animation-name: bounce;">
  <div class="pricing-table">
    <div class="plan featured" style="background: <?php echo esc_attr($price_bg); ?>; border: 2px solid <?php echo esc_attr($top_bg); ?>; transform: scale(1.0<?php echo esc_attr($zoom); ?>);">
      <div class="header" style="background-color: <?php echo esc_attr($top_bg) ?>">
        <h4 class="plan-title" style="color: <?php echo esc_attr($title_clr); ?>; font-size: <?php echo esc_attr($titlesize); ?>px;">
          <?php echo esc_attr($price_title); ?>
        </h4>
        <div class="plan-cost"><span class="plan-price" style="color: <?php echo esc_attr($amount_clr); ?>;font-size: <?php echo esc_attr($amountsize); ?>px;"><?php echo esc_attr($price_currency); ?><?php echo esc_attr($price_amount); ?></span><span class="plan-type" style="color: <?php echo esc_attr($amount_clr); ?>;font-size: <?php echo esc_attr($planesize); ?>px;"><?php echo esc_attr($price_plan); ?></span></div>
        <span class="price-title5-span" style="border-color: <?php echo esc_attr($top_bg) ?> transparent transparent transparent;"></span>
      </div>
      <div class="price-content">
        <?php echo wp_kses_post($content); ?>
      </div>
      <div class="plan-select">
        <a href="<?php echo esc_url($btn_url['url']); ?>" target="<?php echo esc_attr($btn_url['target']); ?>" title="<?php echo esc_html($btn_url['title']); ?>" style="font-size: <?php echo esc_attr($btnsize); ?>px; background: <?php echo esc_attr($top_bg); ?>;"><?php echo esc_attr($btn_text); ?></a>
      </div>
    </div>
  </div>
</div>