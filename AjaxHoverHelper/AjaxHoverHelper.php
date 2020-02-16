<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if ( !defined( 'MEDIAWIKI' ) ) {
        echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/AjaxHoverHelper/AjaxHoverHelper.php" );
EOT;
        exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'AjaxHoverHelper',
	'version' => '0.1',
	'author' => 'Ravhin',
	'url' => 'http://www.destinati.com/',
	'descriptionmsg' => 'ajaxhoverhelper-desc',
);

$dir = dirname( __FILE__ ) . '/';
$wgAutoloadClasses['AjaxHoverHelper'] = $dir . 'AjaxHoverHelper_body.php';
$wgSpecialPages['AjaxHoverHelper'] = 'AjaxHoverHelper';
$wgExtensionMessagesFiles['AjaxHoverHelper'] = $dir . 'AjaxHoverHelper.i18n.php';
$wgExtensionAliasesFiles['AjaxHoverHelper'] = $dir . 'AjaxHoverHelper.alias.php';
