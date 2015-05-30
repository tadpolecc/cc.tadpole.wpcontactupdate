<?php

require_once 'wpcontactupdate.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function wpcontactupdate_civicrm_config( &$config ) {
	_wpcontactupdate_civix_civicrm_config( $config );
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function wpcontactupdate_civicrm_xmlMenu( &$files ) {
	_wpcontactupdate_civix_civicrm_xmlMenu( $files );
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function wpcontactupdate_civicrm_install() {
	_wpcontactupdate_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function wpcontactupdate_civicrm_uninstall() {
	_wpcontactupdate_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function wpcontactupdate_civicrm_enable() {
	_wpcontactupdate_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function wpcontactupdate_civicrm_disable() {
	_wpcontactupdate_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function wpcontactupdate_civicrm_upgrade( $op, CRM_Queue_Queue $queue = NULL ) {
	return _wpcontactupdate_civix_civicrm_upgrade( $op, $queue );
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function wpcontactupdate_civicrm_managed( &$entities ) {
	_wpcontactupdate_civix_civicrm_managed( $entities );
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function wpcontactupdate_civicrm_caseTypes( &$caseTypes ) {
	_wpcontactupdate_civix_civicrm_caseTypes( $caseTypes );
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function wpcontactupdate_civicrm_alterSettingsFolders( &$metaDataFolders = NULL ) {
	_wpcontactupdate_civix_civicrm_alterSettingsFolders( $metaDataFolders );
}

function wpcontactupdate_civicrm_postProcess( $formName, &$form ) {

	$names = array( "CRM_Profile_Form_Edit" );
	if (in_array( $formName, $names ) ) {
      wpcontactupdate_tc_contactupdate();
	}
}
	function wpcontactupdate_civicrm_post( $op, $objectName, $objectId, $objectRef ) {
		if ($op = 'edit'  && $objectName == 'Individual') {
          wpcontactupdate_tc_contactupdate();
	}

		}

function wpcontactupdate_tc_contactupdate() {
  $users = get_users();

  require_once 'CRM/Core/BAO/UFMatch.php';

  $uid  = $user->ID;
  $cuid = get_current_user_id();

  if ( empty( $cuid ) ) {
    return;
  }

  $tc_uf_check = civicrm_api3( 'UFMatch', 'get', array(
    'return'     => "uf_id",
    'uf_id'      => $cuid,
  ) );

  if ( ! empty ( $tc_uf_check['values'] ) ) {
    foreach ( $tc_uf_check['values'] as $key => $value ) {
      $tc_uf_valid = $value['uf_id'];
    }
  }

  if ( empty ( $tc_uf_valid ) ) {
    return;
  }
  $sql     = "SELECT * FROM civicrm_uf_match WHERE uf_id =$cuid";
  $contact = CRM_Core_DAO::executeQuery( $sql );

  if ( $contact->fetch() ) {
    $cid        = $contact->contact_id;
    $conDetails = civicrm_api3( 'Contact', 'get', array(
      'return'       => "id,display_name,first_name,middle_name,last_name,image_URL",
      'contact_type' => "Individual",
      'contact_id'   => $cid
    ) );
    if ( ! empty( $conDetails['values'] ) ) {
      foreach ( $conDetails['values'] as $key => $value ) {
        $conid        = $value['id'];
        $condisname   = $value['display_name'];
        $confirstname = $value['first_name'];
        $conmidname   = $value['middle_name'];
        $conlastname  = $value['last_name'];
        $conrawimage  = $value['image_URL'];
      }
    }

  }
  $tc_cleandefaultimage = str_replace ('&amp;','&',$conrawimage);

  $tc_email = civicrm_api3('Email', 'get', array(
    'return'     => "email",
    'contact_id' => $cid,
    'is_primary' => 1,
  ));
  if (!empty($tc_email['values'])) {
    foreach ($tc_email['values'] as $key => $value) {
      $tcpemail = $value['email'];
    }
  }

  $tc_website_main = civicrm_api3('Website', 'get', array(
    'return' => "url",
    'contact_id' => $cid,
    'website_type_id' => "Main",
  ));
  if (!empty($tc_website_main['values'])) {
    foreach ($tc_website_main['values'] as $key => $value) {
      $tc_website = $value['url'];
    }
  }

  $tc_snickname   = esc_textarea($condisname);
  $tc_sfirst_name = esc_textarea($confirstname);
  $tc_slast_name  = esc_textarea($conlastname);
  $tc_suser_email = sanitize_email($tcpemail);
  $tc_suser_url   = esc_url($tc_website);
  $tc_scleandefaultimage = esc_url($tc_cleandefaultimage);



  $tc_user_up = wp_update_user( array(
    'ID'         => $cuid,
    'nickname'   => $tc_snickname,
    'first_name' => $tc_sfirst_name,
    'last_name'  => $tc_slast_name,
    'user_email' => $tc_suser_email,
    'user_url'   => $tc_suser_url

  ) );

  if ( is_wp_error( $tc_user_up ) ) {
    //echo "There was an error, probably that user doesn't exist";
  }
  else {
    //echo "Success!";
  }

  $tc_user_meta_civiup_di = update_user_meta( $cuid, 'tc_user_civi_default_image', $tc_scleandefaultimage );
  if ( is_wp_error( $tc_user_meta_civiup_di ) ) {
    //echo "There was an error, probably that user doesn't exist";
  }
  else {
    //echo "Success!";
  }

}