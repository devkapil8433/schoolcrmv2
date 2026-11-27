<div class="row appear-animation" data-appear-animation="<?php echo $global_config['animations']; ?>" data-appear-animation-delay="100">
	<div class="col-md-12 mb-lg">
		<div class="profile-head">
			<div class="col-md-12 col-lg-4 col-xl-3">
				<div class="image-content-center user-pro">
					<div class="preview">
						<img src="<?php echo get_image_url('parent', $parent['photo']); ?>" alt="">
					</div>
				</div>
			</div>
			<div class="col-md-12 col-lg-8 col-xl-9">
				<h5><?php echo html_escape($parent['name']); ?></h5>
				<p><?php echo translate('parent'); ?></p>
				<ul>
					<li><div class="icon-holder"><i class="fas fa-bezier-curve"></i></div> <?php echo html_escape($parent['relation']); ?></li>
					<li><div class="icon-holder"><i class="fas fa-user-tag"></i></div> <?php echo html_escape(empty($parent['occupation']) ? 'N/A' : $parent['occupation']); ?></li>
					<li><div class="icon-holder"><i class="fas fa-phone"></i></div> <?php echo html_escape(empty($parent['mobileno']) ? 'N/A' : $parent['mobileno']); ?></li>
					<li><div class="icon-holder"><i class="far fa-envelope"></i></div> <?php echo html_escape(!empty($parent['email']) ? $parent['email'] : 'N/A'); ?></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="far fa-edit"></i> <?php echo translate('profile'); ?></h4>
			</header>
			<?php echo form_open_multipart($this->uri->uri_string()); ?>
			<div class="panel-body">
				<input type="hidden" name="parent_id" value="<?php echo $parent['id']; ?>" id="parent_id">

				<div class="headers-line"><i class="fas fa-user-check"></i> <?php echo translate('parents_details'); ?></div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label"><?php echo translate('name'); ?> <span class="required">*</span></label>
							<input class="form-control" name="name" type="text" value="<?php echo set_value('name', $parent['name']); ?>" autocomplete="off">
							<span class="error"><?php echo form_error('name'); ?></span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label"><?php echo translate('relation'); ?> <span class="required">*</span></label>
							<input type="text" class="form-control" name="relation" value="<?php echo set_value('relation', $parent['relation']); ?>" autocomplete="off">
							<span class="error"><?php echo form_error('relation'); ?></span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label"><?php echo translate('father_name'); ?></label>
							<input class="form-control" name="father_name" type="text" value="<?php echo set_value('father_name', $parent['father_name']); ?>" autocomplete="off">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label"><?php echo translate('mother_name'); ?></label>
							<input type="text" class="form-control" name="mother_name" value="<?php echo set_value('mother_name', $parent['mother_name']); ?>" autocomplete="off">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label"><?php echo translate('occupation'); ?> <span class="required">*</span></label>
							<input type="text" class="form-control" name="occupation" value="<?php echo set_value('occupation', $parent['occupation']); ?>" autocomplete="off">
							<span class="error"><?php echo form_error('occupation'); ?></span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label"><?php echo translate('income'); ?></label>
							<input type="text" class="form-control" name="income" value="<?php echo set_value('income', $parent['income']); ?>" autocomplete="off">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label"><?php echo translate('education'); ?></label>
							<input type="text" class="form-control" name="education" value="<?php echo set_value('education', $parent['education']); ?>" autocomplete="off">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label"><?php echo translate('city'); ?></label>
							<input type="text" class="form-control" name="city" value="<?php echo set_value('city', $parent['city']); ?>" autocomplete="off">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label"><?php echo translate('state'); ?></label>
							<input type="text" class="form-control" name="state" value="<?php echo set_value('state', $parent['state']); ?>" autocomplete="off">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label"><?php echo translate('mobile_no'); ?> <span class="required">*</span></label>
							<input type="text" class="form-control" name="mobileno" value="<?php echo set_value('mobileno', $parent['mobileno']); ?>" autocomplete="off">
							<span class="error"><?php echo form_error('mobileno'); ?></span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label"><?php echo translate('email'); ?></label>
							<input type="email" class="form-control" name="email" id="email" value="<?php echo set_value('email', $parent['email']); ?>">
							<span class="error"><?php echo form_error('email'); ?></span>
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							<label class="control-label"><?php echo translate('address'); ?></label>
							<textarea name="address" rows="2" class="form-control"><?php echo set_value('address', $parent['address']); ?></textarea>
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							<label class="control-label"><?php echo translate('profile_picture'); ?> <span class="required">*</span></label>
							<input type="file" name="user_photo" class="dropify" data-default-file="<?php echo get_image_url('parent', $parent['photo']); ?>">
							<input type="hidden" name="old_user_photo" value="<?php echo html_escape($parent['photo']); ?>">
							<span class="error"><?php echo form_error('user_photo'); ?></span>
						</div>
					</div>
				</div>

				<div class="headers-line mt-md"><i class="fas fa-globe"></i> <?php echo translate('social_links'); ?></div>
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">Facebook</label>
							<input type="text" class="form-control" name="facebook" placeholder="https://www.facebook.com/username" value="<?php echo set_value('facebook', $parent['facebook_url']); ?>">
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">Twitter</label>
							<input type="text" class="form-control" name="twitter" placeholder="https://www.twitter.com/username" value="<?php echo set_value('twitter', $parent['twitter_url']); ?>">
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">LinkedIn</label>
							<input type="text" class="form-control" name="linkedin" placeholder="https://www.linkedin.com/username" value="<?php echo set_value('linkedin', $parent['linkedin_url']); ?>">
						</div>
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<button class="btn btn-default" type="submit"><i class="fas fa-plus-circle"></i> <?php echo translate('update'); ?></button>
			</footer>
			<?php echo form_close(); ?>
		</section>
	</div>
</div>
