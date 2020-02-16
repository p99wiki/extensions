<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if ( !defined( 'MEDIAWIKI' ) ) {
        echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/AuctionTracker/AuctionTracker.php" );
EOT;
        exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'AuctionTracker',
	'version' => '0.1',
	'author' => 'Ravhin',
	'url' => 'http://www.destinati.com/',
	'descriptionmsg' => 'auctiontracker-desc',
);

$dir = dirname( __FILE__ ) . '/';
$wgAutoloadClasses['AuctionTracker'] = $dir . 'AuctionTracker_body.php';
$wgSpecialPages['AuctionTracker'] = 'AuctionTracker';
$wgExtensionMessagesFiles['AuctionTracker'] = $dir . 'AuctionTracker.i18n.php';
$wgExtensionAliasesFiles['AuctionTracker'] = $dir . 'AuctionTracker.alias.php';
