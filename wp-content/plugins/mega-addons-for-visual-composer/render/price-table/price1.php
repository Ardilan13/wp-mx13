<div class="price_table_1" style="background-color: <?php echo esc_attr($price_bg); ?>; box-shadow: 0 0 9px rgba(0,0,0,0.5), 0 -3px 0px <?php echo esc_attr($top_bg); ?> inset;">
	<div class="type" style="background-color: <?php echo esc_attr($top_bg); ?>;">
		<div class="ribbon-right" style="display: <?php echo esc_attr($offer_visibility); ?>;">
			<span style="background: <?php echo esc_attr($offer_bg); ?>;"><?php echo esc_attr($offer_text); ?></span>
		</div>
		<p style="font-size: <?php echo esc_attr($titlesize); ?>px; color: <?php echo esc_attr($title_clr); ?>;">
			<?php echo esc_attr($price_title); ?>
		</p>
	</div>

	<div class="plan">
		<div class="header" style="display: <?php echo esc_attr($price_visibility); ?>;">
			<span class="price_curr" style="color: <?php echo esc_attr($top_bg); ?>">
				<?php echo esc_attr($price_currency); ?>
			</span>
			<span class="amount" style="color: <?php echo esc_attr($top_bg); ?>; font-size: <?php echo esc_attr($amountsize); ?>px;">
				<?php echo esc_attr($price_amount); ?>
			</span>
			<p class="month" style="font-size: <?php echo esc_attr($planesize); ?>px;"><?php echo esc_attr($price_plan); ?></p>
		</div>
		<div class="content">
			<?php echo wp_kses_post($content); ?>
		</div>			
		<div class="price">
      		<a href="<?php echo esc_url($btn_url['url']); ?>" target="<?php echo esc_attr($btn_url['target']); ?>" title="<?php echo esc_html($btn_url['title']); ?>" class="price-btn" style="font-size: <?php echo esc_attr($btnsize); ?>px; background-color: <?php echo esc_attr($top_bg); ?>; box-shadow: inset 0 -2px <?php echo esc_attr($top_bg); ?>;-webkit-box-shadow: inset 0 -2px <?php echo esc_attr($top_bg); ?>;">
      			<?php echo esc_attr($btn_text); ?>
      		</a>
		</div>
	</div>
</div>