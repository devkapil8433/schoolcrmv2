<?php
$branchLogo = $this->application_model->getBranchImage($branch_id, 'logo');
$sideboxImage = !empty($login_sidebox_image) ? html_escape($login_sidebox_image) : '';
?>
<div class="col-lg-5 col-md-6 no-padding hidden-xs hidden-sm">
    <div class="image-area"<?php if ($sideboxImage) { ?> style="background-image: url('<?php echo $sideboxImage; ?>');"<?php } ?>>
        <div class="content text-center">
            <div class="image-hader">
                <span class="welcome-badge"><i class="fas fa-graduation-cap"></i> <?php echo translate('welcome_to'); ?></span>
                <h2><?php echo html_escape($global_config['institute_name']); ?></h2>
            </div>
            <div class="fitxt-center img-hol-p">
                <span class="logo-ring">
                    <img src="<?php echo $branchLogo; ?>" alt="<?php echo htmlspecialchars($global_config['institute_name']); ?>">
                </span>
            </div>
            <div class="address">
                <?php if (!empty($global_config['address'])) { ?>
                <p><i class="fas fa-map-marker-alt"></i> <?php echo html_escape($global_config['address']); ?></p>
                <?php } elseif (!empty($global_config['mobileno'])) { ?>
                <p><i class="fas fa-phone-alt"></i> <?php echo html_escape($global_config['mobileno']); ?></p>
                <?php } ?>
            </div>
            <div class="f-social-links">
                <?php if (!empty($global_config['facebook_url'])) { ?><a href="<?php echo html_escape($global_config['facebook_url']); ?>" target="_blank" rel="noopener"><span><i class="fab fa-facebook-f"></i></span></a><?php } ?>
                <?php if (!empty($global_config['twitter_url'])) { ?><a href="<?php echo html_escape($global_config['twitter_url']); ?>" target="_blank" rel="noopener"><span><i class="fab fa-twitter"></i></span></a><?php } ?>
                <?php if (!empty($global_config['linkedin_url'])) { ?><a href="<?php echo html_escape($global_config['linkedin_url']); ?>" target="_blank" rel="noopener"><span><i class="fab fa-linkedin-in"></i></span></a><?php } ?>
                <?php if (!empty($global_config['youtube_url'])) { ?><a href="<?php echo html_escape($global_config['youtube_url']); ?>" target="_blank" rel="noopener"><span><i class="fab fa-youtube"></i></span></a><?php } ?>
            </div>
        </div>
    </div>
</div>
