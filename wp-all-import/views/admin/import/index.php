<!-- Preload Images -->

<img src="<?php echo PMXI_Plugin::ROOT_URL . '/static/img/soflyy-logo.png'; ?>" class="wpallimport-preload-image"/>

<?php

$l10n = array(
	'queue_limit_exceeded' => 'You have attempted to queue too many files.',
	'file_exceeds_size_limit' => 'This file exceeds the maximum upload size for this site.',
	'zero_byte_file' => 'This file is empty. Please try another.',
	'invalid_filetype' => 'This file type is not allowed. Please try another.',
	'default_error' => 'An error occurred in the upload. Please try again later.',
	'missing_upload_url' => 'There was a configuration error. Please contact the server administrator.',
	'upload_limit_exceeded' => 'You may only upload 1 file.',
	'http_error' => 'HTTP Error: Click here for our <a href="http://www.wpallimport.com/documentation/advanced/troubleshooting/" target="_blank">troubleshooting guide</a>, or ask your web host to look in your error_log file for an error that takes place at the same time you are trying to upload a file.',
	'upload_failed' => 'Upload failed.',
	'io_error' => 'IO error.',
	'security_error' => 'Security error.',
	'file_cancelled' => 'File canceled.',
	'upload_stopped' => 'Upload stopped.',
	'dismiss' => 'Dismiss',
	'crunching' => 'Crunching&hellip;',
	'deleted' => 'moved to the trash.',
	'error_uploading' => 'has failed to upload due to an error',
	'cancel_upload' => 'Cancel upload',
	'dismiss' => 'Dismiss'
);

?>
<script type="text/javascript">
	var plugin_url = '<?php echo WP_ALL_IMPORT_ROOT_URL; ?>';
	var swfuploadL10n = <?php echo json_encode($l10n); ?>;
</script>

<table class="wpallimport-layout wpallimport-step-1">
	<tr>
		<td class="left">
			<div class="wpallimport-wrapper">	
				<h2 class="wpallimport-wp-notices"></h2>
				<div class="wpallimport-header">
					<div class="wpallimport-logo"></div>
					<div class="wpallimport-title">
						<p><?php _e('WP All Import', 'wp_all_import_plugin'); ?></p>
						<h2><?php _e('Import XML / CSV', 'wp_all_import_plugin'); ?></h2>					
					</div>
					<div class="wpallimport-links">
						<a href="http://www.wpallimport.com/support/" target="_blank"><?php _e('Support', 'wp_all_import_plugin'); ?></a> | <a href="http://www.wpallimport.com/documentation/" target="_blank"><?php _e('Documentation', 'wp_all_import_plugin'); ?></a>
					</div>
				</div>			

				<div class="clear"></div>				
				
				<?php if ($this->errors->get_error_codes()): ?>
					<?php $this->error() ?>
				<?php endif ?>				

				<?php //do_action('pmxi_choose_file_header'); ?>

		        <form method="post" class="wpallimport-choose-file" enctype="multipart/form-data" autocomplete="off">
		        	
		        	<div class="wpallimport-upload-resource-step-one">

						<input type="hidden" name="is_submitted" value="1" />						
						
						<div class="clear"></div>											
						
						<div class="wpallimport-import-types" style="margin-top: 13px;">
							<?php if (empty($_GET['deligate'])): ?>
							<div class="wpallimport-content-section wpallimport-console wpallimport-complete-warning" style="    border: none;display: block;margin-top: 0;">
								<h3><?php _e('Upload Neccessary Media before importing', 'wp_all_import_plugin'); ?>
								</h3>
								<h4 style="margin-top: 14px; margin-bottom: 0;"><?php _e('You must upload featured image or author image first before uploading, if image can not be found while importing, the record will not be imported', 'wp_all_import_plugin') ?></h4>
							</div>
							<h2><?php _e('First, specify how you want to import your data', 'wp_all_import_plugin'); ?></h2>
							<?php else: ?>
							<h2 style="margin-bottom: 10px;"><?php _e('First, specify previously exported file', 'wp_all_import_plugin'); ?></h2>
							<h2 class="wp_all_import_subheadline"><?php _e('The data in this file can be modified, but the structure of the file (column/element names) should not change.', 'wp_all_import_plugin'); ?></h2>
							<?php endif; ?>							
							<a class="wpallimport-import-from wpallimport-upload-type <?php echo ('upload' == $post['type'] and ! empty($_POST)) ? 'selected' : '' ?>" rel="upload_type" href="javascript:void(0);">
								<span class="wpallimport-icon"></span>
								<span class="wpallimport-icon-label"><?php _e('Upload a file', 'wp_all_import_plugin'); ?></span>
							</a>
						</div>
						
						<input type="hidden" value="<?php echo $post['type']; ?>" name="type"/>

						<div class="wpallimport-upload-type-container" rel="upload_type">						
							<div id="plupload-ui" class="wpallimport-file-type-options">
					            <div>				                
					                <input type="hidden" name="filepath" value="<?php echo $post['filepath'] ?>" id="filepath"/>
					                <a id="select-files" href="javascript:void(0);"/><?php _e('Click here to select file from your computer...', 'wp_all_import_plugin'); ?></a>
					                <div id="progressbar" class="wpallimport-progressbar">
					                	
					                </div>
					                <div id="progress" class="wpallimport-progress">
					                	<div id="upload_process" class="wpallimport-upload-process"></div>				                	
					                </div>
					            </div>
					        </div>
					        <div class="wpallimport-note" style="margin: 0 auto; font-size: 13px;"></div>					        
						</div>
						<div class="wpallimport-upload-type-container" rel="url_type">						
							<div class="wpallimport-file-type-options">
								<span class="wpallimport-url-icon"></span>
								<input type="text" class="regular-text" name="url" value="<?php echo ( ! empty($post['url'])) ? esc_attr($post['url']) : ''; ?>" placeholder="Enter a web address to download the file from..."/> 
								<a class="wpallimport-download-from-url rad4" href="javascript:void(0);"><?php _e('Download', 'wp_all_import_plugin'); ?></a>
								<span class="img_preloader" style="top:0; left: 5px; visibility: hidden; display: inline;"></span>
							</div>
							<div class="wpallimport-note" style="margin: 20px auto 0; font-size: 13px;">
								<?php _e('<strong>Hint:</strong> After you create this import, you can schedule it to run automatically, on a pre-defined schedule, with cron jobs. If anything in your file has changed, WP All Import can update your site with the changed data automatically.', 'wp_all_import_plugin'); ?>
								<div class="wpallimport-free-edition-notice" style="display:none;">									
									<a href="http://www.wpallimport.com/upgrade-to-pro/?utm_source=free-plugin&utm_medium=in-plugin&utm_campaign=download-from-url" target="_blank" class="upgrade_link"><?php _e('Upgrade to the Pro edition of WP All Import to use this feature.', 'wp_all_import_plugin');?></a>
									<p><?php _e('If you already own it, remove the free edition and install the Pro edition.', 'wp_all_import_plugin'); ?></p>
								</div>
							</div>							
							<input type="hidden" name="downloaded" value="<?php echo $post['downloaded']; ?>"/>
						</div>
						<div class="wpallimport-upload-type-container" rel="file_type">			
							<?php $upload_dir = wp_upload_dir(); ?>					
							<div class="wpallimport-file-type-options">
								
								<div id="file_selector" class="dd-container dd-disabled" style="width: 600px;">
									<div class="dd-select" style="width: 600px; background: none repeat scroll 0% 0% rgb(238, 238, 238);">
										<input type="hidden" class="dd-selected-value" value="">
										<a class="dd-selected" style="color: rgb(207, 206, 202);">
											<label class="dd-selected-text "><?php _e('Select a previously uploaded file', 'wp_all_import_plugin');?></label>
										</a>
										<span class="dd-pointer dd-pointer-down"></span>
									</div>									
								</div>
								
								<input type="hidden" name="file" value="<?php echo esc_attr($post['file']); ?>"/>									
								
								<div class="wpallimport-note" style="margin: 0 auto; font-size: 13px;">
									<?php printf(__('Upload files to <strong>%s</strong> and they will appear in this list', 'wp_all_import_plugin'), $upload_dir['basedir'] . '/wpallimport/files') ?>
									<div class="wpallimport-free-edition-notice">									
										<a href="http://www.wpallimport.com/upgrade-to-pro/?utm_source=free-plugin&utm_medium=in-plugin&utm_campaign=use-existing-file" target="_blank" class="upgrade_link"><?php _e('Upgrade to the Pro edition of WP All Import to use this feature.', 'wp_all_import_plugin');?></a>
										<p><?php _e('If you already own it, remove the free edition and install the Pro edition.', 'wp_all_import_plugin'); ?></p>
									</div>
								</div>
							</div>
						</div>		
						<div id="wpallimport-url-upload-status"></div>				

						<?php if (empty($_GET['deligate'])): ?>	
						
						<div class="wpallimport-upload-resource-step-two">
						</div>
						<?php endif; ?>
					</div>		

					<div class="rad4 first-step-errors error-upload-rejected">
						<div class="wpallimport-notify-wrapper">
							<div class="error-headers exclamation">
								<h3><?php _e('File upload rejected by server', 'wp_all_import_plugin');?></h3>
								<h4><?php _e("Contact your host and have them check your server's error log.", "wp_all_import_plugin"); ?></h4>
							</div>		
						</div>		
						<a class="button button-primary button-hero wpallimport-large-button wpallimport-notify-read-more" href="http://www.wpallimport.com/documentation/troubleshooting/problems-with-import-files/" target="_blank"><?php _e('Read More', 'wp_all_import_plugin');?></a>		
					</div>

					<div class="rad4 first-step-errors error-file-validation" <?php if ( ! empty($upload_validation) ): ?> style="display:block;" <?php endif; ?>>
						<div class="wpallimport-notify-wrapper">
							<div class="error-headers exclamation">
								<h3><?php _e('There\'s a problem with your import file', 'wp_all_import_plugin');?></h3>
								<h4>
									<?php 
									if ( ! empty($upload_validation) ): 										
										$file_type = strtoupper(pmxi_getExtension($post['file']));
										printf(__('Please verify that the file you using is a valid %s file.', 'wp_all_import_plugin'), $file_type); 
									endif;
									?>
								</h4>
							</div>		
						</div>		
						<a class="button button-primary button-hero wpallimport-large-button wpallimport-notify-read-more" href="http://www.wpallimport.com/documentation/troubleshooting/problems-with-import-files/#invalid" target="_blank"><?php _e('Read More', 'wp_all_import_plugin');?></a>		
					</div>	

					<div class="wpallimport-free-edition-notice wpallimport-import-orders-notice" style="text-align:center; margin-top:20px; margin-bottom: 40px; display: none;">
						<a href="http://www.wpallimport.com/upgrade-to-pro/?utm_source=free-plugin&utm_medium=in-plugin&utm_campaign=custom-fields" target="_blank" class="upgrade_link"><?php _e('Upgrade to the Pro edition of WooCommerce add-on to import WooCommerce Orders.', 'wp_all_import_plugin');?></a>
						<p><?php _e('If you already own it, remove the free edition and install the Pro edition.', 'wp_all_import_plugin'); ?></p>
					</div>			

					<p class="wpallimport-submit-buttons">
						<input type="hidden" name="custom_type" value="<?php echo $post['custom_type'];?>">
						<input type="hidden" name="is_submitted" value="1" />
						<input type="hidden" name="auto_generate" value="0" />

						<?php wp_nonce_field('choose-file', '_wpnonce_choose-file'); ?>					
						<a href="javascript:void(0);" class="back rad3 auto-generate-template" style="float:none; background: #e4e6e6; padding: 0 50px;"><?php _e('Skip to Step 4', 'wp_all_import_plugin'); ?></a>			
						<input type="submit" class="button button-primary button-hero wpallimport-large-button" value="<?php _e('Continue to Step 2', 'wp_all_import_plugin') ?>" id="advanced_upload"/>
					</p>
					
					<table><tr><td class="wpallimport-note"></td></tr></table>
				</form>
				<a href="http://soflyy.com/" target="_blank" class="wpallimport-created-by"><?php _e('Created by', 'wp_all_import_plugin'); ?> <span></span></a>
			</div>
		</td>		
	</tr>
</table>