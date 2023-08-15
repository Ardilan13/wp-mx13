<!-- /**** Float Style****/ -->	
<?php if ($member_visibility == 'float') { ?>
	<div class="mega_team_case_2 <?php echo esc_attr($classname); ?>" style="width: <?php echo esc_attr($pro_size); ?>px;">
		<div class="mega_team_head">
			<div class="mega_team_wrap">
				<div class="member-image">
					<?php if (isset($url) && $url != '') { ?>
						<a href="<?php echo esc_url($url['url']); ?>" target="<?php echo esc_attr($url['target']); ?>" title="<?php echo esc_html($url['title']); ?>"><img src="<?php echo esc_attr($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>"></a>
					<?php } ?>
					<?php if (isset($url) && $url == NULL) { ?>
						<a><img src="<?php echo esc_attr($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>"></a>
					<?php } ?>
				</div>
				<div class="member-name" style="color: <?php echo esc_attr($memberproclr); ?>; font-size: <?php echo esc_attr($member_txt_size); ?>px;">
					<?php echo esc_attr($memb_name); ?>
					<span style="background-color: <?php echo esc_attr($member_clr); ?>; color: <?php echo esc_attr($memberproclr); ?>; font-size: <?php echo esc_attr($pro_txt_size); ?>px;">
						<?php echo esc_attr($memb_prof); ?>
					</span>
				</div>
			</div>
		</div>
		<div class="mega_team_footer">
			<div class="member-skills">
				<?php if (!empty($memb_skill)) { ?>
				<div class="skill-label"><?php echo esc_attr($memb_skill); ?></div>
				<div class="skill-prog">
					<div class="fill" data-progress-animation="90%" data-appear-animation-delay="400" style="width: <?php echo esc_attr($memb_perl); ?>%; background-color: <?php echo esc_attr($member_clr); ?>;">
					</div>
				</div>
				<?php } ?>

				<?php if (!empty($memb_skill2)) { ?>
				<div class="skill-label"><?php echo esc_attr($memb_skill2); ?></div>
				<div class="skill-prog">
					<div class="fill" data-progress-animation="90%" data-appear-animation-delay="400" style="width: <?php echo esc_attr($memb_per2); ?>%; background-color: <?php echo esc_attr($member_clr); ?>;">
					</div>
				</div>
				<?php } ?>
				
				<?php if (!empty($memb_skill3)) { ?>
				<div class="skill-label"><?php echo esc_attr($memb_skill3); ?></div>
				<div class="skill-prog">
					<div class="fill" data-progress-animation="90%" data-appear-animation-delay="400" style="width: <?php echo esc_attr($memb_per3); ?>%; background-color: <?php echo esc_attr($member_clr); ?>;">
					</div>
				</div>
				<?php } ?>
				
				<?php if (!empty($memb_skill4)) { ?>
				<div class="skill-label"><?php echo esc_attr($memb_skill4); ?></div>
				<div class="skill-prog">
					<div class="fill" data-progress-animation="90%" data-appear-animation-delay="400" style="width: <?php echo esc_attr($memb_per4); ?>%; background-color: <?php echo esc_attr($member_clr); ?>;">
					</div>
				</div>
				<?php } ?>
				
				<?php if (!empty($memb_skill5)) { ?>
				<div class="skill-label"><?php echo esc_attr($memb_skill5); ?></div>
				<div class="skill-prog">
					<div class="fill" data-progress-animation="90%" data-appear-animation-delay="400" style="width: <?php echo esc_attr($memb_per5); ?>%; background-color: <?php echo esc_attr($member_clr); ?>;">
					</div>
				</div>
				<?php } ?>
			</div>
			<div class="member-info" style="font-size: <?php echo esc_attr($info_size); ?>px; color: <?php echo esc_attr($info_clr); ?>">
				<?php if (!empty($memb_email)) { ?>
					<p><i class="fa fa-envelope" aria-hidden="true"></i> <?php echo esc_attr($memb_email); ?></p>
				<?php } ?>
				<?php if (!empty($memb_url)) { ?>
					<p><i class="fa fa-globe" aria-hidden="true"></i> <?php echo esc_attr($memb_url); ?></p>
				<?php } ?>
				<?php if (!empty($memb_addr)) { ?>
					<p><i class="fa fa-map-marker" aria-hidden="true"></i> <?php echo esc_attr($memb_addr); ?></p>
				<?php } ?>
				<?php if (!empty($memb_numb)) { ?>
					<p><i class="fa fa-phone-square" aria-hidden="true"></i> <?php echo esc_attr($memb_numb); ?></p>
				<?php } ?>
			</div>
			<div class="member-social">
				<a href="<?php echo esc_attr($social_url); ?>" style="background-color: <?php echo esc_attr($social_clr); ?>" target="_blank">
				<i class="<?php echo esc_attr($social_icon); ?>"></i>
				</a>
				<a href="<?php echo esc_attr($social_url2); ?>" style="background-color: <?php echo esc_attr($social_clr2); ?>" target="_blank">
					<i class="<?php echo esc_attr($social_icon2); ?>"></i>
				</a>
				<a href="<?php echo esc_attr($social_url3); ?>" style="background-color: <?php echo esc_attr($social_clr3); ?>" target="_blank">
					<i class="<?php echo esc_attr($social_icon3); ?>"></i>
				</a>
				<a href="<?php echo esc_attr($social_url4); ?>" style="background-color: <?php echo esc_attr($social_clr4); ?>" target="_blank">
					<i class="<?php echo esc_attr($social_icon4); ?>"></i>
				</a>
				<a href="<?php echo esc_attr($social_url5); ?>" style="background-color: <?php echo esc_attr($social_clr5); ?>" target="_blank">
					<i class="<?php echo esc_attr($social_icon5); ?>"></i>
				</a>
				<?php if (!empty($social_icon6)) { ?>
				<a href="<?php echo esc_attr($social_url6); ?>" style="background-color: <?php echo esc_attr($social_clr6); ?>" target="_blank">
					<i class="<?php echo esc_attr($social_icon6); ?>"></i>
				</a>
				<?php } ?>
			</div>
		</div>
		<div class="Clearfix"></div>
		<div class="member-desc" style="color: <?php echo esc_attr($about_clr); ?>; font-size: <?php echo esc_attr($about_txt_size); ?>px;">
			<?php echo esc_attr($memb_about); ?>
		</div>
	</div>
<?php } ?>