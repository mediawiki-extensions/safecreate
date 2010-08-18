<?php
 
if( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

$wgExtensionCredits['other'][] = array( 
	'name' => 'safeCreate', 
	'author' => 'Michael Briganti', 
	'description' => 'Enforces safe password rules on new accounts and password changes.',
	'version' => '1.0.0',
);

$wgHooks['isValidPassword'][] = 'safeCreate';
$wgHooks['PrefsPasswordAudit'][] = 'safeChange';

# Checks all the rules defined in LocalSettings.php
function checkPassword($password, &$result, $user){
	global $wgValidPasswords, $wgContLang, $wgPasswordSpecialChars;
	
	$msg = 'Your password must:<br/><ul>';

	# check for a minimum amount is characters, if needed
	if( $wgValidPasswords['minlength'] )
	{
		$msg = $msg.'<li>Be at least '.$wgValidPasswords['minlength'].' characters</li>';
		if( strlen( $password ) < $wgValidPasswords['minlength'] )
			$result = false;
	}
	
	# check for a lowercase letter, if needed
	if( $wgValidPasswords['lowercase'] )
	{
		$msg = $msg.'<li>Include at least 1 lowercase letter (a-z)</li>';
		if( !preg_match( '/[a-z]/', $password ) )
			$result = false;
	}
	
	# check for an uppercase letter, if needed
	if( $wgValidPasswords['uppercase'] )
	{
		$msg = $msg.'<li>Include at least 1 uppercase letter (A-Z)</li>';
		if( !preg_match( '/[A-Z]/', $password ) )
			$result = false;
	}
	
	# check for a digit, if needed
	if( $wgValidPasswords['digit'] )
	{
		$msg = $msg.'<li>Include at least 1 number (0-9)</li>';
		if( !preg_match( '/[0-9]/', $password ) )
			$result = false;
	}
	
	# check for a special character, if needed
        if( $wgValidPasswords['special'] )
	{
		$msg = $msg.'<li>Include at least 1 special character ('.$wgPasswordSpecialChars.')</li>';
		if( !preg_match( '/[' . $wgPasswordSpecialChars . ']/', $password ) )
			$result = false;
	}
	
	# check for the username, if needed
	if( $wgValidPasswords['usercheck'] )
	{
		$msg = $msg.'<li>Not be the same as your username</li>';
		if( $wgContLang->lc( $password ) == $wgContLang->lc( $user->getName() ) )
			$result = false;
	}
		
	$msg = $msg.'</ul>';
	
	return $msg;
}

# Handles creating a safe password for new accounts
function safeCreate ($password, &$result, $user) {
	global $wgMessageCache, $wgTitle;
	$result = true;
	
	# safeChange handles this page
	if( $wgTitle->getFullText() == 'Special:ChangePassword' )
		return false;
		
	$type = $_GET['type'];
	
	# only on create account page
	if( isset($type) && $type == 'signup' )
	{
		$msg = checkPassword($password, $result, $user);
		
		# add dynamic error message, based on local settings
		$wgMessageCache->addMessages(array('passwordtooshort' => $msg));
	}
	
	return false;
}

# Handles changing to a safe password on Special:ChangePassword
function safeChange($user, $newPass, $error)
{
	global $wgOut;
	
	# Make sure that the change is really a success
	if( $error == 'success' )
	{
		$result = true;
		
		# Check how safe the password is
		$msg = checkPassword($newPass, $result, $user);
		
		# print errors and return to the input prompt
		if( !$result )
		{
			$wgOut->addHTML($msg);
			throw new PasswordError( '' );
		}
		return false;
	}
	return false;
}