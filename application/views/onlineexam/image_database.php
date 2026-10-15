<style type="text/css">
	.image-db-grid { display: flex; flex-wrap: wrap; margin: 0 -8px; }
	.image-db-card-wrap { width: 20%; padding: 8px; }
	.image-db-card { border: 1px solid #e5e5e5; border-radius: 4px; background: #fff; }
	.image-db-thumb { width: 100%; height: 140px; object-fit: cover; border-bottom: 1px solid #eee; background: #fafafa; }
	.image-db-meta { padding: 8px; }
	.image-db-name { font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 6px; }
	@media (max-width: 1200px) { .image-db-card-wrap { width: 25%; } }
	@media (max-width: 992px) { .image-db-card-wrap { width: 33.33%; } }
	@media (max-width: 768px) { .image-db-card-wrap { width: 50%; } }
	@media (max-width: 480px) { .image-db-card-wrap { width: 100%; } }
</style>

<div class="row" id="onlineexamImageDatabasePage" data-branch-id="<?=(int) $image_branch_id?>">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="text-base font-semibold text-slate-900 flex items-center gap-2"><i class="fas fa-images"></i> Image Database</h4>
			</header>
			<div class="panel-body">
				<p class="text-muted mb-md">Store and manage images for use in questions. Upload images here and reuse them in question and option text.</p>
				<div class="alert alert-info">
					<i class="fas fa-info-circle"></i> Use <strong>Insert</strong> while a question editor is open, or copy the URL / HTML snippet and paste it manually.
				</div>
				<div class="row mb-md">
					<div class="col-md-5">
						<?php echo form_open_multipart('onlineexam/image_upload', array('id' => 'imageDbUploadForm')); ?>
							<input type="hidden" name="branch_id" value="<?=(int) $image_branch_id?>">
							<div class="mb-xs">
								<input type="text" class="portal-input" name="image_name" placeholder="Image name (required)" required>
							</div>
							<div class="input-group">
								<input type="file" class="portal-input" name="image" accept=".jpg,.jpeg,.png,.gif,.webp" required>
								<span class="input-group-btn">
									<button type="submit" class="btn-portal-secondary"><i class="fas fa-upload"></i> Upload</button>
								</span>
							</div>
							<p class="text-muted mt-xs mb-none">Allowed: JPG, PNG, GIF, WEBP (max 5MB).</p>
						<?php echo form_close(); ?>
					</div>
					<div class="col-md-4">
						<input type="text" id="imageDbSearch" class="portal-input" placeholder="Search by image name">
					</div>
					<div class="col-md-3 text-right">
						<button type="button" class="btn-portal-secondary" id="imageDbRefresh"><i class="fas fa-rotate"></i> Refresh</button>
						<button type="button" class="btn-portal-secondary" id="imageDbSeedDefaults"><i class="fas fa-wand-magic-sparkles"></i> Seed Defaults</button>
					</div>
				</div>
				<div id="imageDbFeedback" class="mb-sm"></div>
				<div class="panel panel-default">
					<div class="panel-heading">
						<strong>Default Mathematical Shapes</strong>
					</div>
					<div class="panel-body">
						<div id="imageDbDefaultGrid" class="image-db-grid"></div>
					</div>
				</div>
				<div id="imageDbGrid" class="image-db-grid"></div>
				<div class="text-center mt-md">
					<button type="button" class="btn-portal-secondary" id="imageDbLoadMore" style="display:none;">Load More</button>
				</div>
			</div>
		</section>
	</div>
</div>
