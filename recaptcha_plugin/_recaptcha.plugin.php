<?php
/**
 * This file implements the reCaptcha 2.0 plugin.
 *
 * The core functionality was provided by Francois PLANQUE.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2012-2016 by Francois Planque & Others - {@link http://fplanque.com/}.
 *
 * {@internal
 * b2evolution is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * b2evolution is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * }}
 *
 * @package plugins
 *
 * @copyright (c)2010 by Midnight Studios & Others - {@link http://midnightstudios.co.za/}.
 *
 *
 * @author Jacques Joubert @achillis
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


/**
 * The Captcha Plugin.
 *
 * It displays an captcha through {@link CaptchaValidated()} and validates
 * it in {@link CaptchaValidated()}.
 */
class recaptcha_plugin extends Plugin
{
	var $version = '0.0.1';
	var $group = 'antispam';
	var $code = 'recaptcha';
	var $author = 'Jacques Joubert';
	var $help_url = 'https://developers.google.com/recaptcha/';  

	// For testing only!!
	var	$test_key = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';
	var	$test_secret = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';
	/**
	 * Init
	 */
	function PluginInit( & $params )
	{
		$this->name = $this->T_('reCAPTCHA 2.0');
		$this->short_desc = $this->T_('reCAPTCHA protects your site from spam and abuse.');
	}


	/**
	 * Define the GLOBAL settings of the plugin here. These can then be edited in the backoffice in System > Plugins.
	 *
	 * @param array Associative array of parameters (since v1.9).
	 *    'for_editing': true, if the settings get queried for editing;
	 *                   false, if they get queried for instantiating {@link Plugin::$Settings}.
	 * @return array see {@link Plugin::GetDefaultSettings()}.
	 * The array to be returned should define the names of the settings as keys (max length is 30 chars)
	 * and assign an array with the following keys to them (only 'label' is required):
	 */
	function GetDefaultSettings( & $params )
	{
		global $Settings;
		
		return array(
				
				'theme' => array(
					'label' => T_('Theme'),
					'note' =>  $this->T_('Do you want a dark or light widget?'),
					'type' => 'radio',
					'options' => array(
							array( 'light', T_('Light') ),
							array( 'dark', T_('Dark') ) ),
					'defaultvalue' => 'light',
				),
				'use_for_anonymous_comment' => array(
					'label' => $this->T_('Use for anonymous comment forms'),
					'defaultvalue' => 1,
					'note' => $this->T_('Should this plugin be used for anonymous users on comment forms?'),
					'type' => 'checkbox',
				),
				'use_for_registration' => array(
					'label' => $this->T_('Use for new registration forms'),
					'defaultvalue' => 1,
					'note' => $this->T_('Should this plugin be used on registration forms?'),
					'type' => 'checkbox',
				),
				'use_for_anonymous_message' => array(
					'label' => $this->T_('Use for anonymous messaging forms'),
					'defaultvalue' => 1,
					'note' => $this->T_('Should this plugin be used for anonymous users on messaging forms?'),
					'type' => 'checkbox',
				),
				'force_all' => array(
					'label' => $this->T_('Apply for users'),
					'defaultvalue' => 1,
					'note' => $this->T_('Should this plugin be used for anonymous users and members?'),
					'type' => 'checkbox',
				),
				'load_submit' => array(
					'label' => $this->T_('Lazy Load Submit'),
					'defaultvalue' => 1,
					'note' => $this->T_('Submit button is activated after reCaptcha user input?'),
					'type' => 'checkbox',
				),
				'load_submit_type' => array(
					'label' => T_('Lazy Load Submit Type'),
					'note' =>  $this->T_('Visibility or enabled?'),
					'type' => 'radio',
					'options' => array(
							array( 'enable', T_('Enable/Disable') ),
							array( 'visible', T_('Hide/Show') ) ),
					'defaultvalue' => 'visible',
				),
				'key' => array(
					'label' => $this->T_('Site Key'),
					'defaultvalue' => $this->test_key,
					'note' => $this->T_('<a href="https://www.google.com/recaptcha/admin" target="_blank" title="Get your reCaptcha Key">Get it here</a>'),
						'rows' => '1',
						'cols' => '1',
						'type' => 'textarea',
						'allow_empty' => false,
				),
				'secret' => array(
					'label' => $this->T_('Site Secret'),
					'defaultvalue' => $this->test_secret,
					'note' => $this->T_('<a href="https://www.google.com/recaptcha/admin" target="_blank" title="Get your reCaptcha Secret">Get it here</a>'),
						'rows' => '1',
						'cols' => '1',
						'type' => 'textarea',
						'allow_empty' => false,
				),
			);
	}

	
	
	/**
	 * We should let admin know
	 */
	function validKeySecret()
	{
		$test_key = $this->Settings->get( 'key' );
		$test_secret = $this->Settings->get( 'secret' );
		
		if ($test_key == $this->test_key) {
			$this->msg( sprintf( T_('WARNING!!! The reCaptcha Key you provided is for testing purposes only and it will NOT stop bots! %s.'), '<a href="https://developers.google.com/recaptcha/docs/faq#id-like-to-run-automated-tests-with-recaptcha-v2-what-should-i-do" target="_blank" title="Get your reCaptcha Secret">Read more here</a>' ), 'warning' );
		};
		
		if ($test_secret == $this->test_secret) {
			$this->msg( sprintf( T_('WARNING!!! The reCaptcha Secret you provided is for testing purposes only and it will NOT stop bots! %s.'), '<a href="https://developers.google.com/recaptcha/docs/faq#id-like-to-run-automated-tests-with-recaptcha-v2-what-should-i-do" target="_blank" title="Get your reCaptcha Secret">Read more here</a>' ), 'warning' );
		};
		
		return true;
		
	}
	
	/**
	 * Event handler: Called when the admin tries to enable the plugin, changes
	 * its configuration/settings and after installation.
	 *
	 * Use this, if your plugin needs configuration before it can be used.
	 *
	 * @see Plugin::BeforeEnable()
	 * @return true|string True, if the plugin can be enabled/activated,
	 *                     a string with an error/note otherwise.
	 */
	function BeforeEnable()
	{
		
		if( $this->status != 'enabled' ) $this->validKeySecret();
		
		return true;
	}
	
	/**
	 * Update Settings
	 */
	function PluginSettingsUpdateAction()
	{
		if( $this->status == 'enabled' ) $this->validKeySecret();
		
	}
	/**
	 * does array variable contain matching string?
	 * return true or false
	 */
	function string_in_array( $str, $arr )
	{	
		
		foreach( $arr as $item )
		{
			if( is_int(stripos( $str, $item))  ||  is_int( stripos( $item, $str ) ) ) 
			{
				return true;
				
			}
			
		}
		
		return false;
	}
	
	/**
	 * Validate user.
	 *
	 * This event is provided for other plugins and gets used internally
	 * for other events we're hooking into.
	 *
	 * @param array Associative array of parameters.
	 * @return boolean|NULL
	 */
	function CaptchaValidated( & $params )
	{
		
		$params['secret'] = $this->Settings->get( 'secret' );
		$params['force_all'] = $this->Settings->get( 'force_all' );

		if( $params['force_all'] != 1 && (! isset( $params['form_type'] ) || ! $this->does_apply( $params['form_type'] )) )
		{	// We should not apply captcha to the requested form:
			return;
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, [
		'secret' => $params['secret'],
		'response' => $_POST['g-recaptcha-response'],
		'remoteip' => $_SERVER['REMOTE_ADDR']
		]);
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$resp = json_decode(curl_exec($ch));
		
		curl_close($ch);
		
		$error_response = array('missing-input-secret', 'invalid-input-secret', 'missing-input-response', 'invalid-input-response', 'bad-request');
		
		$error_desc = array(T_('The secret parameter is missing.'), T_('The secret parameter is invalid or malformed.'), T_('You need to prove you’re a human!'), T_('The response parameter is invalid or malformed.'), T_('The request is invalid or malformed.'));
		
		if ( $resp->success ) 
		{
			// Success
			return true;
			
		} 
		else {
		// failure
			
			switch( true )
			{
				case ( $this->string_in_array( $error_response[0], $resp->{"error-codes"} ) ):
					
					$params['validate_error'] = $this->T_($error_desc[0]);
					return false;
					
					break;
				case ( $this->string_in_array( $error_response[1], $resp->{"error-codes"} ) ):
					
					$params['validate_error'] = $this->T_($error_desc[1]);
					return false;
					
					break;
				case ( $this->string_in_array( $error_response[2], $resp->{"error-codes"} ) ):
					
					$params['validate_error'] = $this->T_($error_desc[2]);
					return false;
					
					break;
				case ( $this->string_in_array( $error_response[3], $resp->{"error-codes"} ) ):
					
					$params['validate_error'] = $this->T_($error_desc[3]);
					return false;
					
					break;
				case ( $this->string_in_array( $error_response[4], $resp->{"error-codes"} ) ):
					
					$params['validate_error'] = $this->T_($error_desc[4]);
					return false;
					
					break;
					
				default:
					$params['validate_error'] = $this->T_('Unknown Error!');
					return false;
					break;
				
			}	
			
		}
		
		return true;
	}

	/**
	 * When a comment form gets displayed, we inject our captcha.
	 *
	 * @param array Associative array of parameters
	 *   - 'Form': the form where payload should get added (by reference, OPTIONALLY!)
	 *   - 'form_use_fieldset':
	 *   - 'key': A key that is associated to the caller of the event (string, OPTIONALLY!)
	 *   - 'form_type': Form type ( comment|register|message )
	 * @return boolean|NULL true, if displayed; false, if error; NULL if it does not apply
	 */
	function CaptchaPayload( & $params )
	{
		global $DB, $Session;
		
		$params['key'] = $this->Settings->get( 'key' );
		$params['load_submit'] = $this->Settings->get( 'load_submit' );
		$params['load_submit_type'] = $this->Settings->get( 'load_submit_type' );
		
		$params['force_all'] = $this->Settings->get( 'force_all' );

		if( $params['force_all'] != 1 && (! isset( $params['form_type'] ) || ! $this->does_apply( $params['form_type'] )) )
		{	// We should not apply captcha to the requested form:
			return;
		}

		if( ! isset( $params['Form'] ) )
		{	// there's no Form where we add to, but we create our own form:
			$Form = new Form( regenerate_url() );
		}
		else
		{
			$Form = & $params['Form'];
			
		}
		
		$parts = array( 'fieldset_begin' => '</div><!-- End Row -->',
					  'fieldset_end' => '<div class="row"><!-- Start Row -->' );
		/*
		*	To place the reCaptcha widget on a new line
		*	we need to close the previous row and create
		*	a new row. Switch template parts to achieve this.
		*/

		if ( $Form->layout == 'none' ) $Form->switch_template_parts( $parts );
		
		
		
		if( ! isset( $params['Form'] ) )
		{
			
			$Form->begin_form();
		}
		else
		{
			
			if( ! isset( $params['form_use_fieldset'] ) || $params['form_use_fieldset'] )
			{
				$Form->begin_fieldset();
			}
		}
		
		$js = '<script type="text/javascript">'."\n";
		$js .= '$(function(){'."\n";
  		$js .= '\'use strict\';'."\n";
  		$js .= 'var lazySubmit = function() {'."\n";
		
		if( $params['load_submit'] !=0 )
		{
			
			switch( $params['load_submit_type'] )
			{
				case 'enable':
					// disable by default
					$js .= '$("input.submit[type=submit]").attr(\'disabled\',\'disabled\');'."\n";
					break;
					
				default:
					// hide by default
					$js .= '$("input.submit[type=submit]").hide();'."\n";
					break;
			}
			
		}
		$js .= '}'."\n";	
		$js .= 'lazySubmit();'."\n";
		$js .= '})'."\n";
  		$js .= 'var lazySubmited = function() {'."\n";
		
		if( $params['load_submit'] !=0 )
		{
	
			
			switch( $params['load_submit_type'] )
			{
				case 'enable':
					// enable!!
					$js .= 'var attr = $("input.submit[type=submit]").attr(\'disabled\');'."\n";
					// For some browsers, `attr` is undefined; for others, `attr` is false. Check for both.
					$js .= 'if (typeof attr !== typeof undefined && attr !== false) {'."\n";
				  // Element has this attribute
					$js .= '$("input.submit[type=submit]").removeAttr(\'disabled\');'."\n";
					$js .= '}'."\n";
					break;
					
				default:
					// show!!
					$js .= '$("input.submit[type=submit]").show();'."\n";
					break;
			}
			
		}
		
		
		$js .= '}'."\n";
        $js .= 'var onloadCallback = function() {'."\n";
        $js .= 'grecaptcha.render("captcha_widget", {'."\n";
        $js .= '  "sitekey" : "'.$params['key'].'",'."\n";
        $js .= '  "theme" : "'.$this->Settings->get( 'theme' ).'",'."\n";
		$js .= '  "callback" : lazySubmited'."\n";
        $js .= '});'."\n";
        $js .= '};'."\n";
        $js .= '</script>'."\n";
		
		
		
		echo $js;
		
		echo '<div id="captcha_widget" align="center" style="margin: 0 auto;margin-bottom:20px;"></div>';
		
		if( ! isset( $params['Form'] ) )
		{	// there's no Form where we add to, but our own form:
			$Form->end_form( array( array( 'submit', 'submit', $this->T_('Send Message!'), 'ActionButton' ) ) );
		}
		else
		{
			if( ! isset($params['form_use_fieldset']) || $params['form_use_fieldset'] )
			{
				$Form->end_fieldset();
			}
		}

		
		echo '<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>'."\n";
		  
		return true;
	}


	/**
	 * We display our captcha with comment forms.
	 */
	function DisplayCommentFormFieldset( & $params )
	{
		$params['form_type'] = 'comment';
		$this->CaptchaPayload( $params );
	}


	/**
	 * Validate response
	 *
	 * In case of error we add a message of category 'error' which prevents the comment from
	 * being posted.
	 *
	 * @param array Associative array of parameters.
	 */
	function BeforeCommentFormInsert( & $params )
	{
		$params['form_type'] = 'comment';
		$this->validate_form_by_captcha( $params );
	}


	/**
	 * Validate response.
	 *
	 * In case of error we add a message of category 'error' which prevents the comment from
	 * being posted.
	 *
	 * @param array Associative array of parameters.
	 */
	function validate_form_by_captcha( & $params )
	{
		if( ! empty( $params['is_preview'] ) )
		{	// Don't validate on preview action:
			return;
		}

		if( empty( $params['form_type'] ) )
		{	// Form type must be defined:
			return;
		}

		if( $this->CaptchaValidated( $params ) === false )
		{	// Some error on captcha validation:
			$validate_error = $params['validate_error'];
			param_error( 'captcha_qstn_'.$this->ID.'_answer', $validate_error );
		}
	}


	/**
	 * We display our captcha with the register form.
	 */
	function DisplayRegisterFormFieldset( & $params )
	{
		$params['form_type'] = 'register';
		$this->CaptchaPayload( $params );
	}

	/**
	 * Validate response
	 *
	 * In case of error we add a message of category 'error' which prevents the
	 * user from being registered.
	 */
	function RegisterFormSent( & $params )
	{
		$params['form_type'] = 'register';
		$this->validate_form_by_captcha( $params );
	}

	/**
	 * We display our captcha with the message form.
	 */
	function DisplayMessageFormFieldset( & $params )
	{
		
		$params['form_type'] = 'message';
		$this->CaptchaPayload( $params );
		
	}


	/**
	 * Validate response
	 *
	 * In case of error we add a message of category 'error' which prevents the
	 * user from being registered.
	 */
	function MessageFormSent( & $params )
	{
		$params['form_type'] = 'message';
		$this->validate_form_by_captcha( $params );
	}


	/* PRIVATE methods */

	/**
	 * Checks if we should captcha the current request, according to the settings made.
	 *
	 * @param string Form type ( comment|register|message )
	 * @return boolean
	 */
	function does_apply( $form_type )
	{
		switch( $form_type )
		{
			case 'comment':
				
				if( ! is_logged_in() )
				{
					return $this->Settings->get( 'use_for_anonymous_comment' );
				}
				
				break;

			case 'register':
				
				return $this->Settings->get( 'use_for_registration' );

			case 'message':
				
				if( ! is_logged_in() )
				{
					return $this->Settings->get( 'use_for_anonymous_message' );
				}
				
				break;
		}

		return false;
	}

}

?>