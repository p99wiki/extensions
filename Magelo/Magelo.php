<?php
# Not a valid entry point, skip unless MEDIAWIKI is defined
if ( !defined( 'MEDIAWIKI' ) ) {
        echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/Magelo/Magelo.php" );
EOT;
        exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'Magelo',
	'version' => '0.1',
	// You can use array for multiple authors
	'author' => 'Ravhin',
	'url' => 'http://www.destinati.com/',
	'descriptionmsg' => 'magelo-desc',
);

$dir = dirname( __FILE__ ) . '/';
$wgAutoloadClasses['Magelo'] = $dir . 'Magelo_body.php';
$wgSpecialPages['Magelo'] = 'Magelo';
$wgExtensionMessagesFiles['Magelo'] = $dir . 'Magelo.i18n.php';
$wgExtensionAliasesFiles['Magelo'] = $dir . 'Magelo.alias.php';

// hooks
$wgHooks['AlternateEdit'][] = 'Magelo::AlternateEdit';
$wgHooks['ParserAfterTidy'][] = 'Magelo::onParserAfterTidy';
$wgHooks['EditFormPreloadText'][] = 'Magelo::PreloadText';
