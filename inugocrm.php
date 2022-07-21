<?php
/**
 * Plugin Name: Inugo CRM Connection
 * Plugin URI: https://digital-55.com/contact
 * Description: Allows Gravityforms submissions to create leads in the Inugo CRM using the v2 API.
 * Version: 0.1
 * PHP Version: 7.2
 * Author: Dylan Logan
 * Author URI: mailto:dlogan@digital-55.com
 * License: GNU General Public License, version 2 or later.
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

register_uninstall_hook(__FILE__, 'inugoPluginUninstall');
register_activation_hook( __FILE__, 'inugoPluginActivate' );
register_deactivation_hook( __FILE__, 'inugoPluginDeactivate' );

add_action( 'gform_after_submission', 'gravityforms_prepare_data', 10, 2 );

//Remove credentials on plugin uninstall for security reasons.
function inugoPluginUninstall () {
	delete_option( 'inugoUsername' );
	delete_option( 'inugoClientID' );
	delete_option( 'inugoPassword' );
}

function inugoPluginActivate() {
}

function inugoPluginDeactivate() {
}

/**
 *
 *Create an options page
 *
*/
class InugoCRMOptions {
	private $inugoCRMOptions;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'inugo_crm_options_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'inugo_crm_options_page_init' ) );
	}

	public function inugo_crm_options_add_plugin_page() {
		add_options_page(
			'Inugo CRM Options', // page_title
			'Inugo CRM Options', // menu_title
			'manage_options', // capability
			'inugo-crm-options', // menu_slug
			array( $this, 'inugo_crm_options_create_admin_page' ) // function
		);
	}

	public function inugo_crm_options_create_admin_page() {
		$this->inugoCRMOptions = get_option( 'inugo_crm_options_option_name' ); ?>
		<div class="wrap">
			<h2>Inugo CRM Options</h2>
			<p></p>
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
				<?php
					settings_fields( 'inugo_crm_options_option_group' );
					do_settings_sections( 'inugo-crm-options-admin' );
					submit_button();
				?>
			</form>
			                        <?php
                        // This section adds a button for testing the (saved) credentials to see if they are valid by authenticating with the CRM.
                        ?>
                        <p>You must save credentials before testing.</p>
                        <form method="post">
                                <button name="Test_Login" type="submit" value="Test_Login" id="Test_Login" class="button-primary">Test Login</button>
                        </form>
                        <?php
                        if(array_key_exists('Test_Login', $_POST)){
                                //API endpoint for authentication
                                $authurl = "https://crm2.0.priyanet.com/CRMServicesHost/token";
                                //Authenticate With CRM
                                $inugoCRMOptions = get_option( 'inugo_crm_options_option_name' );
                                $loginString = 'grant_type=password&username=' . $inugoCRMOptions['inugoUsername'] . '&password=' . $inugoCRMOptions['inugoPassword'] . '&clientId=' . $inugoCRMOptions['inugoClientID'];
                                $authPost = curl_init();
                                curl_setopt($authPost, CURLOPT_URL, $authurl);
                                curl_setopt($authPost, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($authPost, CURLOPT_HTTPHEADER, array(
                                        'Content-Type: application/x-www-form-urlencoded',
                                        'Accept: application/json'
                                ));
                                curl_setopt($authPost, CURLOPT_POSTFIELDS, $loginString);
                                $authResult = curl_exec($authPost);
                                curl_close($authPost);
                                if($authResult == "{\"error\":\"Wrong credentials..\"}") {
                                        echo("<p style='color: red;'>ERROR: Invalid Credentials Detected</p>");
                                } else {
                                        $authResultArray = json_decode($authResult);
                                        $accessToken = $authResultArray->access_token;
                                        if($accessToken) {
                                                echo("<p style='color: green;'>An access token was successfully retrieved. Your login credentials are valid.</p>");
                                        } else {
                                                echo("<p style='color: red;'>ERROR: Your credentials are not invalid, but you did not recieve an access token!</p>");
                                        }
                                }
                        }
                        ?>
		</div>
	<?php }

	public function inugo_crm_options_page_init() {
		register_setting(
			'inugo_crm_options_option_group', // option_group
			'inugo_crm_options_option_name', // option_name
			array( $this, 'inugo_crm_options_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'inugo_crm_options_setting_section', // id
			'Settings', // title
			array( $this, 'inugo_crm_options_section_info' ), // callback
			'inugo-crm-options-admin' // page
		);

		add_settings_field(
			'inugoUsername', // id
			'Username', // title
			array( $this, 'inugoUsername_callback' ), // callback
			'inugo-crm-options-admin', // page
			'inugo_crm_options_setting_section' // section
		);

		add_settings_field(
			'inugoClientID', // id
			'ClientID', // title
			array( $this, 'inugoClientID_callback' ), // callback
			'inugo-crm-options-admin', // page
			'inugo_crm_options_setting_section' // section
		);

		add_settings_field(
			'inugoPassword', // id
			'Password', // title
			array( $this, 'inugoPassword_callback' ), // callback
			'inugo-crm-options-admin', // page
			'inugo_crm_options_setting_section' // section
		);
	}

	public function inugo_crm_options_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['inugoUsername'] ) ) {
			$sanitary_values['inugoUsername'] = sanitize_text_field( $input['inugoUsername'] );
		}

		if ( isset( $input['inugoClientID'] ) ) {
			$sanitary_values['inugoClientID'] = sanitize_text_field( $input['inugoClientID'] );
		}

		if ( isset( $input['inugoPassword'] ) ) {
			$sanitary_values['inugoPassword'] = sanitize_text_field( $input['inugoPassword'] );
		}

		return $sanitary_values;
	}

	public function inugo_crm_options_section_info() {

	}

	public function inugoUsername_callback() {
		printf(
			'<input class="regular-text" type="text" name="inugo_crm_options_option_name[inugoUsername]" id="inugoUsername" value="%s">',
			isset( $this->inugoCRMOptions['inugoUsername'] ) ? esc_attr( $this->inugoCRMOptions['inugoUsername']) : ''
		);
	}

	public function inugoClientID_callback() {
		printf(
			'<input class="regular-text" type="text" name="inugo_crm_options_option_name[inugoClientID]" id="inugoClientID" value="%s">',
			isset( $this->inugoCRMOptions['inugoClientID'] ) ? esc_attr( $this->inugoCRMOptions['inugoClientID']) : ''
		);
	}

	public function inugoPassword_callback() {
		printf(
			'<input class="regular-text" type="password" name="inugo_crm_options_option_name[inugoPassword]" id="inugoPassword" value="%s">',
			isset( $this->inugoCRMOptions['inugoPassword'] ) ? esc_attr( $this->inugoCRMOptions['inugoPassword']) : ''
		);
	}

}
if ( is_admin() )
	$inugo_crm_options = new InugoCRMOptions();
/*
 * Retrieve options values with:
 * $inugoCRMOptions = get_option( 'inugo_crm_options_option_name' ); // Array of All Options
 * $inugoUsername = $inugoCRMOptions['inugoUsername']; // Username
 * $inugoClientID = $inugoCRMOptions['inugoClientID']; // ClientID
 * $inugoPassword = $inugoCRMOptions['inugoPassword']; // Password
 */

/*
 *
 *This function takes the gravity forms entry data and converts it into the proper array for use with the InugoCRM
 *
*/
function gravityforms_prepare_data( $entry, $form ) {
	//echo '<pre>'; print_r($form); echo '</pre>';
	/*
	* Debug Statement, Print FORM array on form submission.
	*/
	$fullname_field_id = null;
	$prefix_field_id = null;
	$firstname_field_id = null;
	$middlename_field_id = null;
	$lastname_field_id = null;
	$suffix_field_id = null;
	$email_field_id = null;
	$phonenumber_field_id = null;
	$phoneextension_field_id = null;
	$fax_field_id = null;
	$address_field_id = null;
	$address2_field_id = null;
	$city_field_id = null;
	$zipcode_field_id = null;
	$state_field_id = null;
	$country_field_id = null;

	foreach ( $form['fields'] as $field ) {
		if ( ! isset( $field['inugoCRMField'] ) || ! isset( $field['id'] ) ) {
			continue;
		}

		$field_crm_label = $field['inugoCRMField'];
		$field_id = $field['id'];

		switch ( $field_crm_label ) {
			case "crm_fullname":
				$fullname_field_id = $field_id;
				$prefix_field_id = $field_id . ".2";
				$firstname_field_id = $field_id . ".3";
				$middlename_field_id = $field_id . ".4";
				$lastname_field_id = $field_id . ".6";
				$suffix_field_id = $field_id . ".8";
				break;
			case "crm_firstname":
				$firstname_field_id = $field_id;
				break;
			case "crm_lastname":
				$lastname_field_id = $field_id;
				break;
			case "crm_email":
				$email_field_id = $field_id;
				break;
			case "crm_phone":
				$phone_field_id = $field_id;
				break;
			case "crm_phone_extension":
				$phoneextension_field_id = $field_id;
				break;
			case "crm_fax":
				$fax_field_id = $field_id;
				break;
			case "crm_fulladdress":
				$address_field_id = $field_id . '.1';
				$address2_field_id = $field_id . '.2';
				$city_field_id = $field_id . '.3';
				$zipcode_field_id = $field_id . '.5';
				$state_field_id = $field_id . '.4';
				$country_field_id = $field_id . '.6';
				break;
			case "crm_address":
				$address_field_id = $field_id;
				break;
			case "crm_address2":
				$address2_field_id = $field_id;
				break;
			case "crm_city":
				$city_field_id = $field_id;
				break;
			case "crm_zipcode":
				$zipcode_field_id = $field_id;
				break;
			case "crm_county":
				$county_field_id = $field_id;
				break;
			case "crm_state":
				$state_field_id = $field_id;
				break;
			case "crm_country":
				$country_field_id = $field_id;
				break;
		}
		if ($state_field_id == null) {
			$crm_state = "Ohio";
		} else {
			$crm_state = rgar($entry, $state_field_id);
		}
	}
	$data = array(
		"Title"=> rgar( $entry, $prefix_field_id ), //works
		"FirstName"=> rgar( $entry, $firstname_field_id ), //works
		"MiddleName"=> rgar( $entry, $middlename_field_id ),    //Does not fill any fields.
		"LastName"=> rgar( $entry, $lastname_field_id ), //works
		"Phone"=> rgar( $entry, $phone_field_id ), //works
		"PhoneExt"=> rgar( $entry, $phoneextension_id ), //Does not fill any fields. 
		"Fax"=> rgar( $entry, $fax_field_id ), //works
		"Email"=> rgar( $entry, $email_field_id ), //works
		"Gender"=> "", //Does not fill any fields. Required
		"MailingAddress"=> rgar( $entry, $address_field_id ), //works
		"MailingAddress2"=> rgar( $entry, $address2_field_id ), //Does not fill any fields.
		"Mailingcity"=> rgar( $entry, $zipcode_field_id ), //works
		"Mailingzip"=> rgar( $entry, $zipcode_field_id ), //works
		"County"=> rgar( $entry, $county_field_id), //works
		"MailingStateText"=> $crm_state, //works Must be valid
		"MailingCountryText"=> "USA", //works Must be Valid
		"LeadSourcetext"=> "Website", //works
		"LeadStatus"=> "Cold", //works
	);
	post_to_crm( $data );
}
/*
 *
 *Take the data array from gravityforms_prepare_data and send to CRM in proper authenticated post request. 
 *
*/
function post_to_crm( $data ) {

	//API endpoint for authentication
	$authurl = "https://crm2.0.priyanet.com/CRMServicesHost/token";

    	//Authenticate With CRM
	$inugoCRMOptions = get_option( 'inugo_crm_options_option_name' );
	$loginString = 'grant_type=password&username=' . $inugoCRMOptions['inugoUsername'] . '&password=' . $inugoCRMOptions['inugoPassword'] . '&clientId=' . $inugoCRMOptions['inugoClientID'];
	$authPost = curl_init();
	curl_setopt($authPost, CURLOPT_URL, $authurl);
	curl_setopt($authPost, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($authPost, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/x-www-form-urlencoded',
		'Accept: application/json'
	));
	curl_setopt($authPost, CURLOPT_POSTFIELDS, $loginString);
	$authResult = curl_exec($authPost);
	curl_close($authPost);
	$authResultArray = json_decode($authResult);
	$accessToken = $authResultArray->access_token;

	//Uncomment the line below this to print the output. If you see a weird page glitch when submitting a form, comment this line.
	//echo $authResult;

	//API endpoint for posting leads
	$leadsUrl = "https://crm2.0.priyanet.com/CRMServicesHost/api/1/websiteapi/InsertUpdateLeadDetails";
    	//Send the data to the CRM
	$leadPost = curl_init();
	curl_setopt($leadPost, CURLOPT_URL, $leadsUrl);
	$finalData = json_encode($data);
	curl_setopt($leadPost, CURLOPT_POSTFIELDS, $finalData);
	curl_setopt($leadPost, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($leadPost, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($finalData),
		'Authorization: bearer ' . $accessToken
	));

	$postResult = curl_exec($leadPost);
	curl_close($leadPost);
    	// Uncomment the line below this to print the output. If you see a weird page glitch when submitting a form, comment this line.
	//echo $postResult;
}

/*
 *
 *Add a new field to each field in the gravity forms field editor for the CRM field identifier.
 *
 *
*/
add_action( 'gform_field_standard_settings', 'inugoGformSettings', 10, 2 );
function inugoGformSettings( $position, $form_id ) {
  
    //create settings on position 25 (right after Field Label)
    if ( $position == 25 ) {
        ?>
        <li class="inugo_setting field_setting">
                <?php _e("CRM Field", "inugoTextDomain"); ?>
                <?php gform_tooltip("inugoTooltips") ?>
            <input type="text" id="field_crm_value" class="fieldwidth-1" onkeyup="SetFieldProperty('inugoCRMField', jQuery(this).val());" onchange="SetFieldProperty('inugoCRMField', jQuery(this).val());" style="display: block !important;" />
            <label for="field_crm_value" style="display:inline;">
	    </label>
        </li>
        <?php
    }
}
//Action to inject supporting script to the form editor page
add_action( 'gform_editor_js', 'editor_script' );
function editor_script(){
    ?>
    <script type='text/javascript'>
		//adding crm field to fields of types "phone" "text" "name" "email" "textarea" "address"
		fieldSettings.phone += ', .inugo_setting';
		fieldSettings.text += ', .inugo_setting';
		fieldSettings.name += ', .inugo_setting';
		fieldSettings.email += ', .inugo_setting';
		fieldSettings.textarea += ', .inugo_setting';
		fieldSettings.address += ', .inugo_setting';
		//binding to the load field settings event to initialize the checkbox
		jQuery(document).bind('gform_load_field_settings', function(event, field, form){
			jQuery("#field_crm_value").val( rgar( field, 'inugoCRMField' ) );
		});
    </script>
    <?php
}

//Filter to add a new tooltip
add_filter( 'gform_tooltips', 'inugoGFormTooltips' );
function inugoGFormTooltips( $tooltips ) {
   $tooltips['inugoTooltips'] = "<h6>CRM</h6>Input a CRM field code to connect the field to the CRM.";
   return $tooltips;
}
?>
