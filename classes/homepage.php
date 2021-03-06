<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class ACUI_Homepage{
	public static function admin_gui(){
		$args_old_csv = array( 'post_type'=> 'attachment', 'post_mime_type' => 'text/csv', 'post_status' => 'inherit', 'posts_per_page' => -1 );
		$old_csv_files = new WP_Query( $args_old_csv );

		acui_check_options();
?>
	<div class="wrap acui">

		<?php if( $old_csv_files->found_posts > 0 ): ?>
		<div class="postbox">
		    <div title="<?php _e( 'Click to open/close', 'import-users-from-csv-with-meta' ); ?>" class="handlediv">
		      <br>
		    </div>
            <!--
            // XTEC ************ MODIFICAT - Add whitespaces to correcte read
            // 2016.12.21 @xaviernietosanchez
            -->
            <h3 class="hndle"><span>&nbsp;&nbsp;&nbsp;<?php _e( 'Old CSV files uploaded', 'import-users-from-csv-with-meta' ); ?></span></h3>
            <!--
            // ************ FI
            -->
		    <div class="inside" style="display: block;">
		    	<p><?php _e( 'For security reasons you should delete this files, probably they would be visible in the Internet if a bot or someone discover the URL. You can delete each file or maybe you want delete all CSV files you have uploaded:', 'import-users-from-csv-with-meta' ); ?></p>
		    	<input type="button" value="<?php _e( 'Delete all CSV files uploaded', 'import-users-from-csv-with-meta' ); ?>" id="bulk_delete_attachment" style="float:right;" />
		    	<ul>
		    		<?php while($old_csv_files->have_posts()) :
		    			$old_csv_files->the_post();

		    			if( get_the_date() == "" )
		    				$date = "undefined";
		    			else
		    				$date = get_the_date();
		    		?>
		    		<li><a href="<?php echo wp_get_attachment_url( get_the_ID() ); ?>"><?php the_title(); ?></a> <?php _e( 'uploaded on', 'import-users-from-csv-with-meta' ) . ' ' . $date; ?> <input type="button" value="<?php _e( 'Delete', 'import-users-from-csv-with-meta' ); ?>" class="delete_attachment" attach_id="<?php the_ID(); ?>" /></li>
		    		<?php endwhile; ?>
		    		<?php wp_reset_postdata(); ?>
		    	</ul>
		        <div style="clear:both;"></div>
		    </div>
		</div>
		<?php endif; ?>
            <!--
        //XTEC ************ ELIMINAT - Removed to simplify user experience - 19.03.29 @svallde2
        /*
		<div id='message' class='updated'><?php _e( 'File must contain at least <strong>2 columns: username and email</strong>. These should be the first two columns and it should be placed <strong>in this order: username and email</strong>. If there are more columns, this plugin will manage it automatically.', 'import-users-from-csv-with-meta' ); ?></div>
		<div id='message-password' class='error'><?php _e( 'Please, read carefully how <strong>passwords are managed</strong> and also take note about capitalization, this plugin is <strong>case sensitive</strong>.', 'import-users-from-csv-with-meta' ); ?></div>
       */
        //************ FI
        -->
		<div>
			<h2><?php _e( 'Import users from CSV','import-users-from-csv-with-meta' ); ?></h2>
		</div>

		<div style="clear:both;"></div>

		<div class="main_bar">
			<form method="POST" enctype="multipart/form-data" action="" accept-charset="utf-8" onsubmit="return check();">
			<h2><?php _e( 'General', 'import-users-from-csv-with-meta'); ?></h2>
			<table class="form-table">
				<tbody>

				<tr class="form-field form-required">
					<th scope="row"><label><?php _e( 'CSV file <span class="description">(required)</span></label>', 'import-users-from-csv-with-meta' ); ?></th>
					<td>
						<div id="upload_file">
                            <input type="file" name="uploadfiles[]" id="uploadfiles" size="35" class="uploadfiles" />
                            <!--
                            // XTEC ************ AFEGIT - Only show to xtecadmin user
                            // 2017.01.16 @xaviernietosanchez
                            -->
                            <?php if ( is_xtec_super_admin() ){ ?>
                            <!--
                            // ************ FI
                            -->
							<?php _e( '<em>or you can choose directly a file from your host,', 'import-users-from-csv-with-meta' ) ?> <a href="#" class="toggle_upload_path"><?php _e( 'click here', 'import-users-from-csv-with-meta' ) ?></a>.</em>
                            <!--
                            // XTEC ************ AFEGIT - Only show to xtecadmin user
                            // 2017.01.16 @xaviernietosanchez
                            -->
                            <?php } ?>
                            <!--
                            // ************ FI
                            -->
						</div>
						<div id="introduce_path" style="display:none;">
							<input placeholder="<?php _e( 'You have to introduce the path to file, i.e.:' ,'import-users-from-csv-with-meta' ); ?><?php $upload_dir = wp_upload_dir(); echo $upload_dir["path"]; ?>/test.csv" type="text" name="path_to_file" id="path_to_file" value="<?php echo dirname( __FILE__ ); ?>/test.csv" style="width:70%;" />
							<em><?php _e( 'or you can upload it directly from your PC', 'import-users-from-csv-with-meta' ); ?>, <a href="#" class="toggle_upload_path"><?php _e( 'click here', 'import-users-from-csv-with-meta' ); ?></a>.</em>
						</div>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row"><label for="role"><?php _e( 'Default role', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
					<?php
						$list_roles = acui_get_editable_roles();

						foreach ($list_roles as $key => $value) {
							if($key == "subscriber")
								echo "<label style='margin-right:5px;'><input name='role[]' type='checkbox' checked='checked' value='$key'/>$value</label>";
							else
								echo "<label style='margin-right:5px;'><input name='role[]' type='checkbox' value='$key'/>$value</label>";
						}
					?>

					<p class="description"><?php _e( 'You can also import roles from a CSV column. Please read documentation tab to see how it can be done. If you choose more than one role, the roles would be assigned correctly but you should use some plugin like <a href="https://wordpress.org/plugins/user-role-editor/">User Role Editor</a> to manage them.', 'import-users-from-csv-with-meta' ); ?></p>
					</td>
				</tr>
                <!--
               // XTEC ************ AFEGIT - Added link to show help
               // 2016.05.06 @aginard
               -->
                <tr class="form-field form-required">
                    <th scope="row"></th>
                    <td>
                        <a href="javascript:void(0)" onClick="toggleproviderhelp()"><?php _e('Where do I get this info?', 'import-users-from-csv-with-meta') ?></a>
                    </td>
                </tr>
                <!--
                //************ FI
                -->

            <!--
                // XTEC ************ ELIMINAT - Hide comments and options to simply user's experience
                // 2016.05.05 @aginard
                /*
				<tr class="form-field form-required">
					<th scope="row"><label><?php _e( 'What should the plugin do with empty cells?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<select name="empty_cell_action">
							<option value="leave"><?php _e( 'Leave the old value for this metadata', 'import-users-from-csv-with-meta' ); ?></option>
							<option value="delete"><?php _e( 'Delete the metadata', 'import-users-from-csv-with-meta' ); ?></option>
						</select>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row"><label for="user_login"><?php _e( 'Send mail', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<p>
							<?php _e( 'Do you wish to send a mail with credentials and other data?', 'import-users-from-csv-with-meta' ); ?>
							<input type="checkbox" name="sends_email" value="yes" <?php if( get_option( 'acui_manually_send_mail' ) ): ?> checked="checked" <?php endif; ?>>
						</p>
						<p>
							<?php _e( 'Do you wish to send this mail also to users that are being updated? (not only to the one which are being created)', 'import-users-from-csv-with-meta' ); ?>
							<input type="checkbox" name="send_email_updated" value="yes" <?php if( get_option( 'acui_manually_send_mail_updated' ) ): ?> checked="checked" <?php endif; ?>>
						</p>
					</td>
				</tr>

				</tbody>
			</table>
                */
                //************ FI
                -->
			<h2><?php _e( 'Update users', 'import-users-from-csv-with-meta'); ?></h2>

			<table class="form-table">
				<tbody>
				<tr class="form-field form-required">
					<th scope="row"><label><?php _e( 'Update existing users?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<select name="update_existing_users">
							<option value="yes"><?php _e( 'Yes', 'import-users-from-csv-with-meta' ); ?></option>
							<option value="no"><?php _e( 'No', 'import-users-from-csv-with-meta' ); ?></option>
						</select>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label><?php _e( 'Update roles for existing users?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<select name="update_roles_existing_users">
							<option value="no"><?php _e( 'No', 'import-users-from-csv-with-meta' ); ?></option>
							<option value="yes"><?php _e( 'Yes, update and override existing roles', 'import-users-from-csv-with-meta' ); ?></option>
							<option value="yes_no_override"><?php _e( 'Yes, add new roles and not override existing ones', 'import-users-from-csv-with-meta' ); ?></option>
						</select>
					</td>
				</tr>
				</tbody>
			</table>

			<h2><?php _e( 'Users not present in CSV file', 'import-users-from-csv-with-meta'); ?></h2>

			<table class="form-table">
				<tbody>

				<tr class="form-field form-required">
					<th scope="row"><label for="delete_users"><?php _e( 'Delete users that are not present in the CSV?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<div style="float:left; margin-top: 10px;">
							<input type="checkbox" name="delete_users" value="yes"/>
						</div>
						<div style="margin-left:25px;">
							<select id="delete_users_assign_posts" name="delete_users_assign_posts">
								<option value=''><?php _e( 'Delete posts of deled users without assing to any user', 'import-users-from-csv-with-meta' ); ?></option>
								<?php
									$blogusers = get_users( array( 'fields' => array( 'ID', 'display_name' ) ) );

									foreach ( $blogusers as $bloguser ) {
										echo "<option value='{$bloguser->ID}'>{$bloguser->display_name}</option>";
									}
								?>
							</select>
							<p class="description"><?php _e( 'After delete users, we can choose if we want to assign their posts to another user. Please do not delete them or posts will be deleted.', 'import-users-from-csv-with-meta' ); ?></p>
						</div>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label for="change_role_not_present"><?php _e( 'Change role of users that are not present in the CSV?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<div style="float:left; margin-top: 10px;">
							<input type="checkbox" name="change_role_not_present" value="yes"/>
						</div>
						<div style="margin-left:25px;">
							<select id="change_role_not_present_role" name="change_role_not_present_role">
								<?php
									$list_roles = acui_get_editable_roles();

									foreach ($list_roles as $key => $value) {
										echo "<option value='$key'>$value</option>";
									}
								?>
							</select>
							<p class="description"><?php _e( 'After import users which is not present in the CSV and can be changed to a different role.', 'import-users-from-csv-with-meta' ); ?></p>
						</div>
					</td>
				</tr>

				</tbody>
			</table>

			<?php do_action( 'acui_tab_import_before_import_button' ); ?>

			<?php wp_nonce_field( 'acui-import', 'acui-nonce' ); ?>

			<input class="button-primary" type="submit" name="uploadfile" id="uploadfile_btn" value="<?php _e( 'Start importing', 'import-users-from-csv-with-meta' ); ?>"/>
			</form>
		</div>
	</div>
	<script type="text/javascript">
        // XTEC ************ MODIFICAT - Used check function from previous versions, because of the change of checkboxes by dropdown menu
        // 2016.05.06 @aginard
        function check(){
            if(document.getElementById("uploadfiles").value === "") {
                alert ("<?php $msg = _e('Please choose a file', 'import-users-from-csv-with-meta'); echo $msg; ?>");
                return false;
            }
        }
        //************ FI

	jQuery( document ).ready( function( $ ){
		$( ".delete_attachment" ).click( function(){
			var answer = confirm( "<?php _e( 'Are you sure to delete this file?', 'import-users-from-csv-with-meta' ); ?>" );
			if( answer ){
				var data = {
					'action': 'acui_delete_attachment',
					'attach_id': $( this ).attr( "attach_id" )
				};

				$.post(ajaxurl, data, function(response) {
					if( response != 1 )
						alert( "<?php _e( 'There were problems deleting the file, please check file permissions', 'import-users-from-csv-with-meta' ); ?>" );
					else{
						alert( "<?php _e( 'File successfully deleted', 'import-users-from-csv-with-meta' ); ?>" );
						document.location.reload();
					}
				});
			}
		});

		$( "#bulk_delete_attachment" ).click( function(){
			var answer = confirm( "<?php _e( 'Are you sure to delete ALL CSV files uploaded? There can be CSV files from other plugins.', 'import-users-from-csv-with-meta' ); ?>" );
			if( answer ){
				var data = {
					'action': 'acui_bulk_delete_attachment',
				};

				$.post(ajaxurl, data, function(response) {
					if( response != 1 )
						alert( "<?php _e( 'There were problems deleting the files, please check files permissions', 'import-users-from-csv-with-meta' ); ?>" );
					else{
						alert( "<?php _e( 'Files successfully deleted', 'import-users-from-csv-with-meta' ); ?>" );
						document.location.reload();
					}
				});
			}
		});

		$( ".toggle_upload_path" ).click( function( e ){
			e.preventDefault();

			$("#upload_file,#introduce_path").toggle();
		} );

		$("#vote_us").click(function(){
			var win=window.open("http://wordpress.org/support/view/plugin-reviews/import-users-from-csv-with-meta?free-counter?rate=5#postform", '_blank');
			win.focus();
		});

	} );
	</script>

        <!--
        // XTEC ************ AFEGIT - Added Provided Help. Add Jquery library
        // 2015.03.20 @nacho
        // 2017.01.16 @xaviernietosanchez
        -->
        <script>
            function toggleproviderhelp() {
                <?php wp_enqueue_script('jQuery'); ?>
                idp = 'importUsers';
                jQuery('.iu_div_settings_help_' + idp).toggle();
                return false;
            }
        </script>
        <!--
        // ************ FI
        -->

        <!--
        //XTEC ************ AFEGIT - Added block for show help
        //2015.03.20 @nacho
        -->
        <div
                class="iu_div_settings_help_importUsers"
                style="display:none;">
            <table class="form-table editcomment">
                <tbody>
                <tr valign="top">
                    <td>
                        <div id="post-body-content">
                            <div id="namediv" class="stuffbox">
                                <h4 style="padding: 8px 12px; margin: 0.33em 0;">
                                    <label>
                                        <?php _e("Help", "import-users-from-csv-with-meta");?>
                                    </label>
                                </h4>
                                <div class="inside">
                                    <hr class="wsl">
                                    <strong><?php _e("You should fill the first three rows with the next values", "import-users-from-csv-with-meta");?></strong><br/>
                                    <ul><ol>
                                            <li>
                                                <strong>
                                                    <?php _e("Username", "import-users-from-csv-with-meta");?>
                                                </strong>
                                                <?php _e("Sets the username.", "import-users-from-csv-with-meta");?>
                                            </li>
                                            <li>
                                                <strong>
                                                    <?php _e("Email", "import-users-from-csv-with-meta");?>
                                                </strong>
                                                <?php _e("Sets user email.", "import-users-from-csv-with-meta");?>
                                            </li>
                                            <li>
                                                <strong>
                                                    <?php _e("Password", "import-users-from-csv-with-meta");?>
                                                </strong>
                                                <?php _e("Sets user password.", "import-users-from-csv-with-meta");?>
                                            </li>
                                    </ul></ol>

                                    <strong><?php _e("The next columns are totally customizable and you can use whatever you want. All rows must contains same columns", "import-users-from-csv-with-meta");?></strong><br/>

                                    <ol>
                                        <li>
                                            <strong>
                                                <?php _e("user_nicename", "import-users-from-csv-with-meta");?>
                                            </strong>
                                            <?php _e("A string that contains a URL-friendly name for the user. The default is the user's username.", "import-users-from-csv-with-meta");?>
                                        </li>
                                        <li>
                                            <strong>
                                                <?php _e("user_url", "import-users-from-csv-with-meta");?>
                                            </strong>
                                            <?php _e("A string containing the user's URL for the user's web site.", "import-users-from-csv-with-meta");?>
                                        </li>
                                        <li>
                                            <strong>
                                                <?php _e("display_name", "import-users-from-csv-with-meta");?>
                                            </strong>
                                            <?php _e("A string that will be shown on the site. Defaults to user's username. It is likely that you will want to change this, for both appearance and security through obscurity (that is if you dont use and delete the default admin user).", "import-users-from-csv-with-meta");?>
                                        </li>
                                        <li>
                                            <strong>
                                                <?php _e("nickname", "import-users-from-csv-with-meta");?>
                                            </strong>
                                            <?php _e("The user's nickname, defaults to the user's username.", "import-users-from-csv-with-meta");?>
                                        </li>
                                        <li>
                                            <strong>
                                                <?php _e("first_name", "import-users-from-csv-with-meta");?>
                                            </strong>
                                            <?php _e("The user's first name.", "import-users-from-csv-with-meta");?>
                                        </li>
                                        <li>
                                            <strong>
                                                <?php _e("last_name", "import-users-from-csv-with-meta");?>
                                            </strong>
                                            <?php _e("The user's last name.", "import-users-from-csv-with-meta");?>
                                        </li>
                                        <li>
                                            <strong>
                                                <?php _e("description", "import-users-from-csv-with-meta");?>
                                            </strong>
                                            <?php _e("A string containing content about the user.", "import-users-from-csv-with-meta");?>
                                        </li>
                                    </ol>
                                    </hr>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td width="10"></td>
                    <td width="400"> </td>
                </tr>
                </tbody>
            </table>
        </div>
        <!--
        */
        //************ FI
        -->
	<?php
	}
}