<?php 

if ( ! defined( 'ABSPATH' ) ) exit; 

/* Most part of this file is based in the original one wp_mail_smtp.php from the plugin WP Mail SMTP that you can find here https://wordpress.org/plugins/wp-mail-smtp/ */

global $acui_smtp_options;
$acui_smtp_options = array (
	'acui_settings' => 'wordpress',
	'acui_mail_from' => '',
	'acui_mail_from_name' => '',
	'acui_mailer' => 'smtp',
	'acui_mail_set_return_path' => 'false',
	'acui_smtp_host' => 'localhost',
	'acui_smtp_port' => '25',
	'acui_smtp_ssl' => 'none',
	'acui_smtp_auth' => false,
	'acui_smtp_user' => '',
	'acui_smtp_pass' => ''
);

function acui_smtp() {
	global $acui_smtp_options, $phpmailer;
	
	// Send a test mail if necessary
	if (isset($_POST['acui_smpt_action']) && $_POST['acui_smpt_action'] == __('Send Test', 'acui') && isset($_POST['to'])) {
		
		check_admin_referer('test-email');

		if ( !is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ) ) {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			require_once ABSPATH . WPINC . '/class-smtp.php';
			$phpmailer = new PHPMailer( true );
		}

		add_action( 'phpmailer_init', 'acui_mailer_init' );
		
		// Set up the mail variables
		$to = $_POST['to'];
		$subject = 'Import Users From CSV With Meta - Mail SMTP: ' . __('Test mail to ', 'acui') . $to;
		$message = __('This is a test email generated by the Import User From CSV With Meta WordPress plugin.', 'acui');
		
		// Set SMTPDebug to true
		$phpmailer->SMTPDebug = true;
		
		// Start output buffering to grab smtp debugging output
		ob_start();

		add_filter( 'wp_mail_from', 'acui_mail_from' );
		add_filter( 'wp_mail_from_name', 'acui_mail_from_name' );
		add_filter( 'wp_mail_content_type', 'set_html_content_type' );
						
		$result = wp_mail( $to, $subject , $message );

		remove_filter( 'wp_mail_from', 'acui_mail_from' );
		remove_filter( 'wp_mail_from_name', 'acui_mail_from_name' );
		remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
		
		// Strip out the language strings which confuse users
		//unset($phpmailer->language);
		// This property became protected in WP 3.2
		
		// Grab the smtp debugging output
		$smtp_debug = ob_get_clean();				
	?>
<div id="message" class="updated fade">
	<?php if( $result ): ?>
	
	<p><strong><?php _e('Message sent successfully', 'acui'); ?></strong></p>
	
	<?php else: ?>

	<p><strong><?php _e('Test Message Sent', 'acui'); ?></strong></p>
	<p><?php _e('The result was:', 'acui'); ?></p>
	<pre><?php var_dump( $result ); ?></pre>
	<p><?php _e('The full debugging output is shown below:', 'acui'); ?></p>
	<pre><?php var_dump( $phpmailer ); ?></pre>
	<p><?php _e('The SMTP debugging output is shown below:', 'acui'); ?></p>
	<pre><?php echo $smtp_debug ?></pre>

	<?php endif; ?>
</div>
	
<?php 		
		// Destroy $phpmailer so it doesn't cause issues later
		unset($phpmailer);
		remove_action( 'phpmailer_init', 'acui_mailer_init' );
	}

	if (isset($_POST['option_page']) && $_POST['option_page'] == 'acui-smtp' ) {
		check_admin_referer('email-config');

		foreach ($acui_smtp_options as $name => $val) {
			update_option( $name, $_POST[ $name ] );			
		}

	}

	// in version 1.8.7 we include this new option, we fill it in a smart way
	if( get_option( "acui_settings" ) == "" ){
		if( get_option( "acui_mail_from" ) == "" )
			update_option( "acui_settings", "wordpress" );
		else
			update_option( "acui_settings", "plugin" );
	}
?>
	
<div class="wrap">
	<h2><?php _e('Import User From CSV With Meta - SMTP server options', 'acui'); ?></h2>
	<form method="post" action="" id="acui_smtp_options">
		<?php wp_nonce_field('email-config'); ?>

		<h3><?php _e('Global options', 'acui'); ?></h3>
		<p><?php _e('Do you want to use your own SMTP settings for this plugin or the WordPress settings.', 'acui'); ?></p>

		<table class="optiontable form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Settings', 'acui'); ?> </th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span><?php _e('Use plugin SMTP settings', 'acui'); ?></span></legend>
						<p><input id="acui_settings_plugin" type="radio" name="acui_settings" value="plugin" <?php checked('plugin', get_option('acui_settings')); ?> />
						<label for="acui_settings"><?php _e('Use this settings to send mail.', 'acui'); ?></label></p>
						<p><input id="acui_settings_wordpress" type="radio" name="acui_settings" value="wordpress" <?php checked('wordpress', get_option('acui_settings')); ?> />
						<label for="acui_settings"><?php _e('Use WordPress general settings to send mail.', 'acui'); ?></label></p>
					</fieldset>
				</td>
			</tr>
		</table>

		<table class="optiontable form-table">
			<tr valign="top">
				<th scope="row"><label for="mail_from"><?php _e('From Email', 'acui'); ?></label></th>
				<td><input name="acui_mail_from" type="text" id="acui_mail_from" value="<?php print(get_option('acui_mail_from')); ?>" size="40" class="regular-text" />
					<span class="description"><?php _e('You can specify the email address that emails should be sent from. If you leave this blank, the default email will be used.', 'acui'); if(get_option('db_version') < 6124) { print('<br /><span style="color: red;">'); _e('<strong>Please Note:</strong> You appear to be using a version of WordPress prior to 2.3. Please ignore the From Name field and instead enter Name&lt;email@domain.com&gt; in this field.', 'acui'); print('</span>'); } ?></span></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mail_from_name"><?php _e('From Name', 'acui'); ?></label></th>
				<td><input name="acui_mail_from_name" type="text" id="acui_mail_from_name" value="<?php print(get_option('acui_mail_from_name')); ?>" size="40" class="regular-text" />
					<span class="description"><?php _e('You can specify the name that emails should be sent from. If you leave this blank, the emails will be sent from WordPress.', 'acui'); ?></span></td>
			</tr>
		</table>

		<table class="optiontable form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Mailer', 'acui'); ?> </th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span><?php _e('Mailer', 'acui'); ?></span></legend>
						<p><input id="acui_mailer_smtp" type="radio" name="acui_mailer" value="smtp" <?php checked('smtp', get_option('acui_mailer')); ?> />
						<label for="mailer_smtp"><?php _e('Send emails of this plugin via SMTP.', 'acui'); ?></label></p>
						<p><input id="acui_mailer_mail" type="radio" name="acui_mailer" value="mail" <?php checked('mail', get_option('acui_mailer')); ?> />
						<label for="mailer_mail"><?php _e('Use the PHP mail() function to send emails.', 'acui'); ?></label></p>
					</fieldset>
				</td>
			</tr>
		</table>

		<table class="optiontable form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Return Path', 'acui'); ?> </th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span><?php _e('Return Path', 'acui'); ?></span></legend><label for="mail_set_return_path">
						<input name="acui_mail_set_return_path" type="checkbox" id="acui_mail_set_return_path" value="true" <?php checked('true', get_option('acui_mail_set_return_path')); ?> />
						<?php _e('Set the return-path to match the From Email'); ?></label>
					</fieldset>
				</td>
			</tr>
		</table>

		<h3><?php _e('SMTP Options', 'acui'); ?></h3>
		<p><?php _e('These options only apply if you have chosen to send mail by SMTP above.', 'acui'); ?></p>

		<table class="optiontable form-table">
			<tr valign="top">
				<th scope="row"><label for="smtp_host"><?php _e('SMTP Host', 'acui'); ?></label></th>
				<td><input name="acui_smtp_host" type="text" id="acui_smtp_host" value="<?php print(get_option('acui_smtp_host')); ?>" size="40" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="smtp_port"><?php _e('SMTP Port', 'acui'); ?></label></th>
				<td><input name="acui_smtp_port" type="text" id="acui_smtp_port" value="<?php print(get_option('acui_smtp_port')); ?>" size="6" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Encryption', 'acui'); ?> </th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span><?php _e('Encryption', 'acui'); ?></span></legend>
						<input id="acui_smtp_ssl_none" type="radio" name="acui_smtp_ssl" value="none" <?php checked('none', get_option('acui_smtp_ssl')); ?> />
						<label for="smtp_ssl_none"><span><?php _e('No encryption.', 'acui'); ?></span></label><br />
						<input id="acui_smtp_ssl_ssl" type="radio" name="acui_smtp_ssl" value="ssl" <?php checked('ssl', get_option('acui_smtp_ssl')); ?> />
						<label for="smtp_ssl_ssl"><span><?php _e('Use SSL encryption.', 'acui'); ?></span></label><br />
						<input id="acui_smtp_ssl_tls" type="radio" name="acui_smtp_ssl" value="tls" <?php checked('tls', get_option('acui_smtp_ssl')); ?> />
						<label for="smtp_ssl_tls"><span><?php _e('Use TLS encryption. This is not the same as STARTTLS. For most servers SSL is the recommended option.', 'acui'); ?></span></label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Authentication', 'acui'); ?> </th>
				<td>
					<input id="acui_smtp_auth_false" type="radio" name="acui_smtp_auth" value="false" <?php checked('false', get_option('acui_smtp_auth')); ?> />
					<label for="smtp_auth_false"><span><?php _e('No: Do not use SMTP authentication.', 'acui'); ?></span></label><br />
					<input id="acui_smtp_auth_true" type="radio" name="acui_smtp_auth" value="true" <?php checked('true', get_option('acui_smtp_auth')); ?> />
					<label for="smtp_auth_true"><span><?php _e('Yes: Use SMTP authentication.', 'acui'); ?></span></label><br />
					<span class="description"><?php _e('If this is set to no, the values below are ignored.', 'acui'); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="smtp_user"><?php _e('Username', 'acui'); ?></label></th>
				<td><input name="acui_smtp_user" type="text" id="acui_smtp_user" value="<?php print(get_option('acui_smtp_user')); ?>" size="40" class="code" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="smtp_pass"><?php _e('Password', 'acui'); ?></label></th>
				<td><input name="acui_smtp_pass" type="password" id="acui_smtp_pass" value="<?php print(get_option('acui_smtp_pass')); ?>" size="40" class="code" /></td>
			</tr>
		</table>

		<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" /></p>
			<input type="hidden" name="action" value="update" />
		</p>
		
		<input type="hidden" name="option_page" value="acui-smtp">
	</form>

	<h3><?php _e('Send a Test Email', 'acui'); ?></h3>

	<form method="POST" action="<?php echo admin_url( 'tools.php?page=acui-smtp' ); ?>">
		<?php wp_nonce_field('test-email'); ?>
		<table class="optiontable form-table">
		<tr valign="top">
		<th scope="row"><label for="to"><?php _e('To:', 'acui'); ?></label></th>
		<td><input name="to" type="text" id="to" value="" size="40" class="code" />
		<span class="description"><?php _e('Type an email address here and then click Send Test to generate a test email.', 'acui'); ?></span></td>
		</tr>
		</table>
		<p class="submit"><input type="submit" name="acui_smpt_action" id="acui_smpt_action" class="button-primary" value="<?php _e('Send Test', 'acui'); ?>" /></p>
	</form>
</div>

<script>
jQuery( document ).ready( function( $ ){
	$( "[name='acui_settings']" ).on('change', function() {
	   var selected = $( 'input[name=acui_settings]:checked' ).val();

	   if( selected == "wordpress" )
	   		disableControls();
	   	else
	   		enableControls();
	});

	function disableControls(){
		$("#acui_smtp_options :input").prop("disabled", true);
		$("[name='acui_settings']").prop("disabled", false);
	}

	function enableControls(){
		$("#acui_smtp_options :input").prop("disabled", false);	
	}

	<?php if( get_option( "acui_settings" ) == "wordpress" ): ?>
		disableControls();
	<?php else: ?>
		enableControls();
	<?php endif; ?>
})
</script>

	<?php
}

function acui_mailer_init( PHPMailer $phpmailer ){
	if ( ! get_option('acui_mailer') || ( get_option('acui_mailer') == 'smtp' && ! get_option('acui_smtp_host') ) ) {
		return;
	}
	
	// Set the mailer type as per config above, this overrides the already called isMail method
	$phpmailer->Mailer = get_option('acui_mailer');
	
	// Set the Sender (return-path) if required
	if (get_option('acui_mail_set_return_path'))
		$phpmailer->Sender = $phpmailer->From;
	
	// Set the SMTPSecure value, if set to none, leave this blank
	$phpmailer->SMTPSecure = get_option('acui_smtp_ssl') == 'none' ? '' : get_option('acui_smtp_ssl');
	
	// If we're sending via SMTP, set the host
	if (get_option('acui_mailer') == "smtp") {
		
		// Set the SMTPSecure value, if set to none, leave this blank
		$phpmailer->SMTPSecure = get_option('acui_smtp_ssl') == 'none' ? '' : get_option('acui_smtp_ssl');
		
		// Set the other options
		$phpmailer->Host = get_option('acui_smtp_host');
		$phpmailer->Port = get_option('acui_smtp_port');
		
		// If we're using smtp auth, set the username & password
		if (get_option('acui_smtp_auth') == "true") {
			$phpmailer->SMTPAuth = TRUE;
			$phpmailer->Username = get_option('acui_smtp_user');
			$phpmailer->Password = get_option('acui_smtp_pass');
		}
	}

	$phpmailer = apply_filters('acui_smtp_custom_options', $phpmailer);
}	