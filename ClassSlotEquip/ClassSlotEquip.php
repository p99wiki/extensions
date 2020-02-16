<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if ( !defined( 'MEDIAWIKI' ) ) {
        echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/ClassSlotEquip/ClassSlotEquip.php" );
EOT;
        exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'ClassSlotEquip',
	'version' => '0.1',
	// You can use array for multiple authors
	'author' => 'Ravhin',
	'url' => 'http://www.destinati.com/',
	'descriptionmsg' => 'classslotequip-desc',
);

$dir = dirname( __FILE__ ) . '/';
$wgAutoloadClasses['ClassSlotEquip'] = $dir . 'ClassSlotEquip_body.php';
$wgSpecialPages['ClassSlotEquip'] = 'ClassSlotEquip';
$wgExtensionMessagesFiles['ClassSlotEquip'] = $dir . 'ClassSlotEquip.i18n.php';
$wgExtensionAliasesFiles['ClassSlotEquip'] = $dir . 'ClassSlotEquip.alias.php';
