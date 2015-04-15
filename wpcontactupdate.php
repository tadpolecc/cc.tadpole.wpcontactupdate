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

	if ( ! in_array( $formName, $names ) ) {
		$users = get_users();

		require_once 'CRM/Core/BAO/UFMatch.php';

		$uid  = $user->ID;
		$cuid = get_current_user_id();

		if ( empty( $cuid ) ) {
			return;
		}

		$tc_uf_check = civicrm_api3( 'UFMatch', 'get', array(
			'sequential' => 1,
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
				'sequential'   => 1,
				'return'       => "id,display_name,first_name,middle_name,last_name,custom_9",
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
					$conimage     = $value['custom_9'];
				}
			}

		}

		$tc_file = $result = civicrm_api3( 'File', 'get', array(
			'sequential' => 1,
			'return'     => "id,uri",
			'id'         => $conimage,
		) );
		if ( ! empty( $tc_file['values'] ) ) {
			foreach ( $tc_file['values'] as $key => $value ) {
				$tcid  = $value['id'];
				$tcuri = $value['uri'];
			}
		}

		$tc_imagedir = $result = civicrm_api3( 'Setting', 'get', array(
			'sequential' => 1,
			'return'     => "customFileUploadDir",
		) );
		if ( ! empty( $tc_imagedir['values'] ) ) {
			foreach ( $tc_imagedir['values'] as $key => $value ) {
				$tcimageurl = $value['customFileUploadDir'];
			}
		}

		$tc_upload_dir      = wp_upload_dir();
		$tc_profile_dirname = $tc_upload_dir['basedir'] . '/' . 'profile-images';
		if ( ! file_exists( $tc_profile_dirname ) ) {
			wp_mkdir_p( $tc_profile_dirname );
		}

		$tc_baseurl            = get_bloginfo( 'url' ) . '/';
		$tc_basedir            = plugin_dir_url() . 'files/civicrm/custom/';
		$tc_wp_civi_profiledir = $tc_upload_dir['baseurl'] . '/' . 'profile-images/';
		$tc_buildurl           = $tc_basedir . $tcuri;
		$tc_wp_buildurl        = $tc_wp_civi_profiledir . $tcuri;
		$tc_civi_pimage        = $tcimageurl . $tcuri;
		$tc_wp_pimagedir       = $tc_profile_dirname . '/';
		$tc_wp_pimageurl       = $tc_wp_pimagedir . $tcuri;
		copy( $tc_civi_pimage, $tc_wp_pimageurl );


		$tc_user_up = wp_update_user( array(
			'ID'         => $cuid,
			'nickname'   => $condisname,
			'first_name' => $confirstname,
			'last_name'  => $conlastname
		) );

		if ( is_wp_error( $tc_user_up ) ) {
			//echo "There was an error, probably that user doesn't exist";
		} else {
			//echo "Success!";
		}

		$tc_user_meta_civiup = update_user_meta( $cuid, 'tc_user_civi_image', $tc_buildurl );
		if ( is_wp_error( $tc_user_meta_civiup ) ) {
			//echo "There was an error, probably that user doesn't exist";
		} else {
			//echo "Success!";
		}

		$tc_user_meta_wpup = update_user_meta( $cuid, 'tc_user_wp_image', $tc_wp_buildurl );
		if ( is_wp_error( $tc_user_meta_wpup ) ) {
			//echo "There was an error, probably that user doesn't exist";
		} else {
			//echo "Success!";
		}
	}
}