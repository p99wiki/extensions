p99wiki Extensions
Copyright (C) 2013 Dylan Nelson (dnelson@destinati.com)

License
-------

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, version 3. This license is available
in its entirety at <http://www.gnu.org/licenses/>.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

About
-----

Five custom mediawiki extensions focused on more semantic presentation
of wiki pages and content, as well as supplementary custom DBs. Tested on
MediaWiki 1.15.3 but should work fine on later versions. Some make a 
new SpecialPage available, whereas automatically inject content into normal 
wiki pages, or are meant to be transcluded. For an example of all these 
extensions working live see <http://wiki.project1999.org>.

Dependencies
------------

Possibly "Labeled Section Transclusion", "Dynamic Page List", 
"ParserFunctions", and certainly "CreateBox".

Installation
------------

Copy to your /mediawiki/extensions/ folder and add the following lines to 
LocalSettings.php:

	# namespaces
	define("NS_MAGELO_BLUE", 500);
	define("NS_MAGELO_BLUE_TALK", 501);
	define("NS_MAGELO_RED", 502);
	define("NS_MAGELO_RED_TALK", 503);
 
	$wgExtraNamespaces[NS_MAGELO_BLUE] = "Magelo_Blue";
	$wgExtraNamespaces[NS_MAGELO_BLUE_TALK] = "Magelo_Blue_talk";   // underscore required
	$wgExtraNamespaces[NS_MAGELO_RED] = "Magelo_Red";
	$wgExtraNamespaces[NS_MAGELO_RED_TALK] = "Magelo_Red_talk";   // underscore required
	$wgNamespacesToBeSearchedDefault = array(NS_MAIN => true, NS_MAGELO_BLUE => true, NS_MAGELO_RED => true);

	# extensions
	require_once( "$IP/extensions/ClassSlotEquip/ClassSlotEquip.php" );
	require_once( "$IP/extensions/AjaxHoverHelper/AjaxHoverHelper.php" );
	require_once( "$IP/extensions/AuctionTracker/AuctionTracker.php" );
	require_once( "$IP/extensions/DynamicZoneList/DynamicZoneList.php" );
	require_once( "$IP/extensions/Magelo/Magelo.php" );
	require_once( "$IP/extensions/CreateBox/CreateBox.php" ); // for magelo creation
	$wgAllowExternalImagesFrom = 'http://wiki.project1999.org/'; // for Auction charts	
	
End.