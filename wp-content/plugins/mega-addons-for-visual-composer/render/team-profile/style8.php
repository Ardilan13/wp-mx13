<div class="mega_team_case_8 <?php echo esc_attr($classname); ?>">
    <div class="maw_team_wrap" style="background: <?php echo esc_attr($member_clr); ?>; max-width: <?php echo esc_attr($pro_size); ?>px; width: 100%;">
        <div class="maw_team_photo_wrapper">
            <div class="maw_team_photo">
                <div class="">
                    <img src="<?php echo esc_attr($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>">
                </div>
            </div>
        </div>
        
        <div class="maw_team_description">
            <span class="maw_team_name" style="color: <?php echo esc_attr($memberproclr); ?>; font-size: <?php echo esc_attr($member_txt_size); ?>px;">
                <?php echo esc_attr($memb_name); ?>
            </span>
            <span class="maw_team_role" style="color: <?php echo esc_attr($memberproclr); ?>; font-size: <?php echo esc_attr($pro_txt_size); ?>px;">
                <?php echo esc_attr($memb_prof); ?>
            </span>
            <div class="maw_team_text" style="color: <?php echo esc_attr($about_clr); ?>; font-size: <?php echo esc_attr($about_txt_size); ?>px;">
                <?php echo esc_attr($memb_about); ?>
            </div>
        </div>

        <div class="maw_team_icons">
            <?php if (!empty($social_icon)) { ?>
                <a class="maw_team_icon" href="<?php echo esc_attr($social_url); ?>" style="color: <?php echo esc_attr($social_clr); ?>;" target="_blank">
                    <i aria-hidden="true" class="<?php echo esc_attr($social_icon); ?>" style="font-size: <?php echo esc_attr($social_size); ?>px;"></i>
                </a>
            <?php } ?>
            <?php if (!empty($social_icon2)) { ?>
                <a class="maw_team_icon" href="<?php echo esc_attr($social_url2); ?>" style="color: <?php echo esc_attr($social_clr2); ?>;" target="_blank">
                    <i aria-hidden="true" class="<?php echo esc_attr($social_icon2); ?>" style="font-size: <?php echo esc_attr($social_size); ?>px;"></i>
                </a>
            <?php } ?>
            <?php if (!empty($social_icon3)) { ?>
                <a class="maw_team_icon" href="<?php echo esc_attr($social_url3); ?>" style="color: <?php echo esc_attr($social_clr3); ?>;" target="_blank">
                    <i aria-hidden="true" class="<?php echo esc_attr($social_icon3); ?>" style="font-size: <?php echo esc_attr($social_size); ?>px;"></i>
                </a>
            <?php } ?>
            <?php if (!empty($social_icon4)) { ?>
                <a class="maw_team_icon" href="<?php echo esc_attr($social_url4); ?>" style="color: <?php echo esc_attr($social_clr4); ?>;" target="_blank">
                    <i aria-hidden="true" class="<?php echo esc_attr($social_icon4); ?>" style="font-size: <?php echo esc_attr($social_size); ?>px;"></i>
                </a>
            <?php } ?>
            <?php if (!empty($social_icon5)) { ?>
                <a class="maw_team_icon" href="<?php echo esc_attr($social_url5); ?>" style="color: <?php echo esc_attr($social_clr5); ?>;" target="_blank">
                    <i aria-hidden="true" class="<?php echo esc_attr($social_icon5); ?>" style="font-size: <?php echo esc_attr($social_size); ?>px;"></i>
                </a>
            <?php } ?>
        </div>
    </div>
</div>