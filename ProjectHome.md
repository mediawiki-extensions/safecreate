This extension works in 2 ways to help users set more secure passwords (with password rules are setup in LocalSettings.php):

  * Requiring new account to conform to the password rules
  * Requiring changed passwords on the Special:ChangePassword page to conform to the password rules

Features:

  * Does not require any customizations to the Mediawiki core code, nor does it require any customizations to the database.
> > o Easy to switch back to the default Mediawiki authentication.
  * Allows users who are already in the database (who might have password that does not conform with the rules) to log in normally.