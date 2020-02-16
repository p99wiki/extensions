<?php
		 
/*************************************************

p99wiki - extensions (DynamicZoneList)
Copyright (C) 2013 Dylan Nelson (dnelson@destinati.com)
Version: 0.1

* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, version 3. This license is available
* in its entirety at <http://www.gnu.org/licenses/>.

* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.

*************************************************/			 
		 
class DynamicZoneList extends SpecialPage {

function __construct()
{
  //parent::__construct( "DynamicZoneList" );
	//to allow transclusion of this special page, ($name,$restriction,$listed,$function,$file,$includable);
  parent::__construct("DynamicZoneList","",true,false,'default',true);
}

function parseTemplateParameters($templateText)
{
  $cbrackets = 2;
  $size = strlen( $templateText );
  $parms = array();
  $parm = '';
  $hasParm = false; 

  for ( $i = 0; $i < $size; $i++ )
  {
    $c = $templateText[$i];

    if ( $c == '{' || $c == '[' ) {
      $cbrackets++; // we count both types of brackets
    }

    if ( $c == '}' || $c == ']' ) {
      $cbrackets--;
    }

    if ( $cbrackets == 4 && $c == '|' ) {
      $parms[] = trim( $parm );
      $hasParm = true;
      $parm = '';
    } else {
      $parm .= $c;
    }

    if ( $cbrackets == 0 ) {
      if ( $hasParm ) {
        $parms[] = trim( substr( $parm, 0, strlen( $parm ) - 2 ) );
      }
      //array_splice( $parms, 0, 1 ); // remove artifact; 
    }
  }

  return $parms;
}

function getCatIntersection($db,$parser,$catNames)
{
  $iCatCount = count($catNames);
  
  $poptions = new ParserOptions;
  
  foreach ($catNames as $catName)
  {
  	$title = Title::newFromText( $parser->transformMsg($catName, $poptions) );
  	if( is_null( $title ) ) {
      $wgOut->addWikiText("Error! 8605");
      return;
  	}
  	$aCategories[] = $title;
  }
	
  // query 
	$aTables = Array( 'page' );
	$aFields = Array( 'page_namespace', 'page_title' );
	$aWhere = Array();
	$aJoin = Array();
	$aOptions = Array();
  
	$aOptions['ORDER BY'] = "page_title ASC";
	//$aOptions['LIMIT'] = 0;
	//$aOptions['OFFSET'] = 0;
  
	$iCurrentTableNumber = 1;
	$categorylinks = $db->tableName( 'categorylinks' );

	for ($i = 0; $i < $iCatCount; $i++) {
		$aJoin["$categorylinks AS c$iCurrentTableNumber"] = Array( 'INNER JOIN',
			Array( "page_id = c{$iCurrentTableNumber}.cl_from",
			 	"c{$iCurrentTableNumber}.cl_to={$db->addQuotes($aCategories[$i]->getDBKey())}"
			)
		);
		$aTables[] = "$categorylinks AS c$iCurrentTableNumber";

		$iCurrentTableNumber++;
	}

  // retrieve list of pages in this category intersection
	$res = $db->select( $aTables, $aFields, $aWhere, __METHOD__, $aOptions, $aJoin );
	
	//if ( $db->numRows( $res ) == 0 ) {
	//	$wgOut->addWikiText("No items found.");
  //  return;
	//}

  return $res;
}

function parseQuestPage($templateText)
{
		$regex = "/class=\"questTopTable\"(.*?)\|}\n/s";
		$ret = preg_match($regex, $templateText, $block);		
		
		if (!$ret) {
		  $rowVals = array("?","","","","","");
			return $rowVals;
		}
		
		// quest giver
		$pos = strpos($block[0],"Quest Giver");
		$pos1 = strpos($block[0],"\n",$pos);
		$pos2 = strpos($block[0],"\n",$pos1+1);
		$questGiver = trim(substr($block[0],$pos1+2,$pos2-$pos1-2));
		
		// minimum level
		$pos = strpos($block[0],"Minimum Level");
		$pos1 = strpos($block[0],"\n",$pos);
		$pos2 = strpos($block[0],"\n",$pos1+1);
		$minLevel = trim(substr($block[0],$pos1+2,$pos2-$pos1-2));
		
		// classes
		$pos = strpos($block[0],"Classes");
		$pos1 = strpos($block[0],"\n",$pos);
		$pos2 = strpos($block[0],"\n",$pos1+1);
		$classes = trim(substr($block[0],$pos1+2,$pos2-$pos1-2));
		
		// related zones
		$pos = strpos($block[0],"Related Zones");
		$pos1 = strpos($block[0],"\n",$pos);
		$pos2 = strpos($block[0],"\n",$pos1+1);
		$relZones = trim(substr($block[0],$pos1+2,$pos2-$pos1-2));
		
		// related NPCs
		$pos = strpos($block[0],"Related NPCs");
		$pos1 = strpos($block[0],"\n",$pos);
		$pos2 = strpos($block[0],"\n",$pos1+1);
		$relNPCs = trim(substr($block[0],$pos1+2,$pos2-$pos1-2));
		
		// reward
		$regex = "/Reward(.*?)<\/ul>/s";
		$ret = preg_match($regex, $templateText, $block);
		
		if (!$ret) {
			$reward = "?";
		} else {
		  $regex = "/<li>(.*?)<\/li>/s";
		  $ret = preg_match_all($regex, $block[0], $subBlock);
			
			$reward = "";
			foreach ($subBlock[1] as $rew) {
			  $reward .= trim($rew) . ", ";
			}
			$reward = substr($reward,0,strlen($reward)-2);
			//$reward = $subBlock[1][0];
		}
		
    $rowVals = array($reward,$questGiver,$minLevel,$classes,$relZones,$relNPCs);

		return $rowVals;
}

function parseNPCPage($templateText)
{
		$maxDescLen = 120;
		$maxNumLootItems = 4;

    $parms = DynamicZoneList::parseTemplateParameters($templateText);
		
    foreach ($parms as $parm) {
      if ( strpos($parm,"race") === 0) {
        $ePos = strpos($parm,"=");
        $race = trim(substr($parm,$ePos+1));
      }
      if ( strpos($parm,"class") === 0) {
        $ePos = strpos($parm,"=");
        $class = trim(substr($parm,$ePos+1));
      }
      if ( strpos($parm,"level") === 0) {
        $ePos = strpos($parm,"=");
        $level = trim(substr($parm,$ePos+1));
      }
      if ( strpos($parm,"location") === 0) {
        $ePos = strpos($parm,"=");
        $location = trim(substr($parm,$ePos+1));
      }
      if ( strpos($parm,"description") === 0) {
        $ePos = strpos($parm,"=");
        $desc = trim(substr($parm,$ePos+1));
      }
      if ( strpos($parm,"known_loot") === 0) {
        $ePos = strpos($parm,"=");
        $loot = trim(substr($parm,$ePos+1));
      }
    }
		
		// parse description
		if (strlen($desc) > $maxDescLen) {
		  $desc = substr($desc,0,$maxDescLen) . "...";
		}
		
		// parse loot
		if (strpos($loot,"None") !== False) {
		  $loot = "<span class='drare'>''None''</span>";
		} else {
		  $regex = "/<li>(.*?)<\/li>/s";
		  $ret = preg_match_all($regex, $loot, $subBlock);
			
			if ($ret) {
			  // if 3 or less items, make a comma separated list
			  if (count($subBlock[1]) > $maxNumLootItems) {
			    $loot = "Various";
				} else {
				  $loot = "";
					foreach ($subBlock[1] as $lt) {
					  // strip out additional rarity/db info from loot row
						if (strpos($lt,"<span class='ddb'>")) {
							$lt = substr($lt,0,strpos($lt,"<span class='ddb'>"));
						}
						if (strpos($lt,"<span class='drare'>")) {
							$lt = substr($lt,0,strpos($lt,"<span class='drare'>"));
						}
						
						// add to comma separated list
						$loot .= trim($lt) . ", ";
					}
					$loot = substr($loot,0,strlen($loot)-2);

				}
		  }
		}
		
		if ($class == "[[Shopkeeper]]") {
		  $loot = "<span class='drare'>''(Merchant)''</span>";
		}
		
		$rowVals = array($race,$class,$level,$location,$loot,$desc);
		
		return $rowVals;
}

function parseItemPage($templateText,$zoneName)
{
  $weapSlots = array("Primary",  
                     "Seconary", 
                     "Range",    
                     "Ammo",     
                     "1H Blunt",    
                     "2H Blunt",    
                     "1H Slashing", 
                     "2H Slashing", 
                     "Piercing",    
                     "Archery",     
                     "Hand to Hand");
						
		// defaults
		$npc   = "?";
		$slot  = "?";
		$stats = "?";
		
		// remove {{VeliousGray| (.*?) }} before parsing
		$templateText = preg_replace("/\* {{VeliousGray\|(.*?)}}\n/","",$templateText);
		$templateText = preg_replace("/{{VeliousGray\|(.*?)}}\n/","",$templateText);
		
		// parse {{Itempage}} template parameters
		$parms = ClassSlotEquip::parseTemplateParameters($templateText);
		
    $statsBlock = '';
    $dropsFrom  = '';		
		
    foreach ($parms as $parm) {
      if ( strpos($parm,"statsblock") === 0) {
        $ePos = strpos($parm,"=");
        $statsBlock = trim(substr($parm,$ePos+1));
      }
      if ( strpos($parm,"dropsfrom") === 0) {
        $ePos = strpos($parm,"=");
        $dropsFrom = trim(substr($parm,$ePos+1));
      }
    }
		
		//foreach ($parms as $parm)
		//  print "<br>" . $parm . "<br>";
		
		// parse for slot
		$regex = "/Slot:(.*?)<br>\n/";
		$ret = preg_match($regex, $statsBlock, $block);
		
		if ($ret) {
			$slot = ucfirst(strtolower(trim($block[1])));
				
			if ($slot == "Finger") $slot = "Fingers";
				
			// split on space for multiple slots
			$slots = explode(" ",$slot);
			$slot = "";
			
			foreach ($slots as $addslot) {
			  $addslot = ucfirst(strtolower(trim($addslot)));
			  $slot .= "[[:Category:" . $addslot . "|" . $addslot . "]], ";
			}
			$slot = substr($slot,0,-2);
		} else {
			$slot = "<span class='drare'>(None)</span>";
		}
		
		// override slot with weapon type if applicable
		$weapTag = "";
		
		foreach ($weapSlots as $weapSlot) {
				if (strpos($statsBlock,"Skill: ".$weapSlot)) {
						$slot = "[[:Category:" . $weapSlot . "|" . $weapSlot . "]]";
						$weapTag = $weapSlot;
				}
		}

    // parse statsblock
    //$rowVals = ClassSlotEquip::parseStatsBlock($statsBlock, $dropsFrom, $slot);
		
		// create "stats" string
		$start = strpos($statsBlock,"Slot:");
		if ($start !== false) {
		  $start = strpos($statsBlock,"<br>",$start);
			$statsBlock = substr($statsBlock,$start+5);
		}
		
		$end = strpos($statsBlock,"Slot ");
		if ($end)
		  $statsBlock = substr($statsBlock,0,$end);
		
		if ($weapTag)
			$statsBlock = str_replace("Skill: ".$weapTag,"",$statsBlock);
			
		$statsBlock = str_replace("Any Slot/Can Equip, ","",$statsBlock);
		$statsBlock = str_replace("(Any Slot, Casting Time: Instant)","",$statsBlock);
		
		// replace newlines by spaces
		$stats = str_replace("<br>"," ",$statsBlock);
		$stats = str_replace("\n","",$stats);
		
		// tag replace with stylized tags
		$sTag = "'''";
		$eTag = "'''";
		
		$tags = array("AC:","Atk Delay:","DMG:","WT:","Size:","Class:","Race:",
		              "STR:","STA:","AGI:","DEX:","CHA:","INT:","WIS:","HP:","MANA:",
									"SV MAGIC:","SV FIRE:","SV COLD:","SV POISON:","SV DISEASE:");
		
		foreach ($tags as $tag)
			$stats = str_replace($tag,$sTag.$tag.$eTag,$stats);
								
		// make list of mobs that drop this item
		$maxNumNPCs = 3;
		$mobList = array();
		
		$start = strpos($dropsFrom,$zoneName . "]]");
		
		if ($start) {
				// starting subset
				$dropsFrom = substr($dropsFrom,$start+strlen($zoneName)+3);
				
				// ending subset
				$end = strpos($dropsFrom,"\n[["); // next zone listing in |dropsfrom
				if ($end)
						$dropsFrom = substr($dropsFrom,0,$end);
						
				// if 3 or less mobs, make a comma separated list
				if (substr_count($dropsFrom,"*") > $maxNumNPCs) {
						$npc = "Various";
				} else {
						$dropsFrom = trim($dropsFrom);
								
						$dropsFrom = str_replace("* ","",$dropsFrom);
						$dropsFrom = str_replace("*","",$dropsFrom);
						$dropsFrom = str_replace("\n",", ",$dropsFrom);
						
						$npc = $dropsFrom;
				}
						
				
		} else {
				$npc = "<span style='color:green;'>None?</span>";
		}
		
		$rowVals = array($npc,$slot,$stats);
		
		return $rowVals;
}

function execute( $par )
{
  global $wgRequest, $wgOut, $wgUser, $wgParser, $wgTitle;
	global $wgDBserver, $wgDBname, $wgDBuser, $wgDBpassword;

	$zoneNameList = array(// ANTONICA
                  "Befallen",
                  "Blackburrow",
                  "Cazic Thule",
                  "Clan Runnyeye",
                  "East Commonlands",
                  "East Freeport",
                  "Eastern Plains of Karana",
                  "Erud's Crossing",
                  "Everfrost Peaks",
                  "Beholder's Maze",
                  "Grobb",
                  "Halas",
                  "Highpass Keep",
                  "Highpass Hold",
                  "Innothule Swamp",
                  "Kithicor Forest",
                  "Splitpaw Lair",
                  "Lake Rathetear",
                  "Lavastorm Mountains",
                  "Lower Guk",
                  "Misty Thicket",
                  "Rathe Mountains",
                  "Nagafen's Lair",
                  "Najena",
                  "Nektulos Forest",
                  "Neriak Commons",
                  "Neriak Foreign Quarter",
                  "Neriak Third Gate",
                  "North Freeport",
                  "Northern Karana",
                  "North Qeynos",
                  "Northern Desert of Ro",
                  "Oasis of Marr",
                  "Ocean of Tears",
                  "Oggok",
                  "Permafrost",
                  "Qeynos Aqueducts",
                  "Qeynos Hills",
                  "Rivervale",
                  "Solusek's Eye",
                  "Southern Karana",
                  "South Qeynos",
                  "Southern Desert of Ro",
                  "Surefall Glade",
                  "Temple of Solusek Ro",
                  "The Feerrott",
                  "Upper Guk",
                  "West Commonlands",
                  "West Freeport",
                  "Western Plains of Karana",
                  
                  // ODUS
                  "Erudin",
                  "Erudin Palace",
                  "Kerra Island",
                  "Paineel",
                  "Toxxulia Forest",
                  
                  // FAYDWER
                  "Ak'Anon",
                  "Butcherblock Mountains",
                  "Mistmoore Castle",
                  "Crushbone",
                  "Dagnor's Cauldron",
                  "The Estate of Unrest",
                  "Greater Faydark",
                  "Kedge Keep",
                  "Lesser Faydark",
                  "North Kaladim",
                  "Northern Felwithe",
                  "South Kaladim",
                  "Southern Felwithe",
                  "Steamfont Mountains",
                  
                  // PLANES
                  "Plane of Fear",
                  "Plane of Hate",
                  "Plane of Sky",
									
									// KUNARK
									"Burning Woods",
                  "Chardok",
                  "City of Mist",
                  "Crypt of Dalnir",
                  "Dreadlands",
                  "East Cabilis",
                  "Emerald Jungle",
                  "Field of Bone",
                  "Firiona Vie",
                  "Frontier Mountains",
                  "Howling Stones",
                  "Kaesora",
                  "Karnor's Castle",
                  "Kurn's Tower",
                  "Lake of Ill Omen",
                  "Mines of Nurga",
                  "Old Sebilis",
                  "Skyfire Mountains",
                  "Swamp of No Hope",
                  "Temple of Droga",
                  "The Overthere",
                  "Timorous Deep",
                  "Trakanon's Teeth",
                  "Veeshan's Peak",
                  "Warsliks Woods",
                  "West Cabilis",
                  "The Hole", // Odus, post-Kunark

									// VELIOUS
									"Cobalt Scar",
                  "Crystal Caverns",
                  "Dragon Necropolis",
                  "Eastern Wastes",
                  "Great Divide",
                  "Iceclad Ocean",
                  "Icewell Keep",
                  "Kael Drakkel",
                  "Kerafyrm's Lair",
                  "Plane of Growth",
                  "Plane of Mischief",
                  "Siren's Grotto",
                  "Skyshrine",
									"Sleeper's Tomb",
                  "Temple of Veeshan",
                  "The Wakening Land",
                  "Thurgadin",
                  "Tower of Frozen Shadow",
                  "Velketor's Labyrinth",
                  "Western Wastes",
                  "Stonebrunt Mountains", // Odus, post-Velious
                  "The Warrens", // Odus, post-velious
                  "Jaggedpine Forest" //Antonica, post-velious
                  );	
	
	ini_set('display_errors', 1);
	error_reporting(E_ERROR | E_PARSE); //E_WARNING
	
	// config
	
	// connect to DB
  $SQLcon = mysql_connect($wgDBserver, $wgDBuser, $wgDBpassword);
  $DBcon = mysql_select_db($wgDBname);
	
  $parser = new Parser;	
  $db = wfGetDB( DB_SLAVE );
	
  // parse and sanitize zone name
  $zoneName = trim($par);
	//$zoneName = mysql_real_escape_string($zoneName);
	if (strlen($zoneName) > 40) { $zoneName = substr($zoneName,0,40); }
	$zoneName = str_replace("_"," ",$zoneName);
	
	// default page
	// ------------
  if (!$zoneName) {
	  $output = "<span>Hello! This extension is meant to be transcluded into zone pages. <br><br>It takes one parameter (the name of the zone).</span>";

		$wgOut->setPagetitle("Project 1999 Dynamic Zone List");
    $wgOut->addHTML($output);
    return;
  }
	
  if (!in_array($zoneName,$zoneNameList)) {
	  $output = "'''DynamicZoneList: Invalid zone name!'''";
    $wgOut->addWikiText($output);
    return;
  }	
	
	// DB search for pages
	// -------------------
  $catNames = array($zoneName,"Quests");
	$quests = DynamicZoneList::getCatIntersection($db,$parser,$catNames);
	
  $catNames = array($zoneName,"NPCs");
	$NPCs = DynamicZoneList::getCatIntersection($db,$parser,$catNames);
	
  $catNames = array($zoneName,"Items");
	$items = DynamicZoneList::getCatIntersection($db,$parser,$catNames);

	// output header
	// ------
	$output = "";
	
	// output quests
	// -------------
	if ($db->numRows($quests))
	{
		$output .= "'''Quests''' - ''Found " . $db->numRows($quests) . " quests that start in " . $zoneName . ":''";
		
		//table header
		$output .= "\n<table class='eoTable3 sortable' style='width:100%;'>";
		$output .= "\n<tr><th>Quest Name</th>";
		$output .=       "<th>Reward</th>" . 
										 "<th>Quest Giver</th>" . 	
										 "<th>Minimum Level</th>" . 
										 "<th>Classes</th>" . 
										 "<th>Related Zones</th>" . 
										 "<th>Related NPCs</th>" . 							 
										 "</tr>";
	 
		foreach ( $quests as $row )
		{
			$title = Title::makeTitle( $row->page_namespace, $row->page_title);
			$titleText = $title->getText();
			
			if ($titleText == "Faction")
			  continue;

			// get quest header block
			$tTitle = Title::newFromText( $titleText );
			$article = new Article($tTitle);
			$templateText = $article->getContent();
			//$templateText = preg_replace( '/<!--.*?-->/s', '', $parser->fetchTemplate( $tTitle ) );

			if (strpos($templateText,"{{Velious Era}}") !== false)
			  continue;
			if (strpos($templateText,"{{WarrensFearHateRevamp Era}}") !== false)
			  continue;
			if (strpos($templateText,"{{StonebruntChardokRevamp Era}}") !== false)
			  continue;
				
			$rowVals = DynamicZoneList::parseQuestPage($templateText);
			
			// output row
			$output = $output . "\n<tr><td> [[" . $titleText . "]]\n</td>";
			for ($i=0; $i < count($rowVals); $i++)
			{
				$output = $output . "\n    <td> " . $rowVals[$i] . " </td>";
			}
			$output .= "</tr>\n";
		}

		$output .= "</table>\n";
	} else {
		//$output .= "''\nDidn't find any quests that start in " . $zoneName . "!''";
	}
	
	// output NPCs
	// -----------
	if ($db->numRows($NPCs))
	{
		$output .= "\n\n'''NPCs''' - ''Found " . $db->numRows($NPCs) . " NPCs that spawn in " . $zoneName . ":''";
		
		//table header
		$output .= "\n<table class='eoTable3 sortable' style='width:100%;'>";
		$output .= "\n<tr><th>NPC Name</th>";
		$output .=       "<th>Race</th>" . 
										 "<th>Class</th>" . 	
										 "<th>Level</th>" . 
										 "<th>Location</th>" . 
										 "<th>Known Loot</th>" . 
										 "<th>Description</th>" . 							 
										 "</tr>";
	 
		foreach ( $NPCs as $row )
		{
			$title = Title::makeTitle( $row->page_namespace, $row->page_title);
			$titleText = $title->getText();

			// get quest header block
			$tTitle = Title::newFromText( $titleText );
			$article = new Article($tTitle);
			$templateText = $article->getContent();
			//$templateText = preg_replace( '/<!--.*?-->/s', '', $parser->fetchTemplate( $tTitle ) );

			if (strpos($templateText,"{{Velious Era}}") !== false)
			  continue;
			
			$rowVals = DynamicZoneList::parseNPCPage($templateText);
			
			// output row
			$output = $output . "\n<tr><td> [[" . $titleText . "]]\n</td>";
			for ($i=0; $i < count($rowVals); $i++)
			{
				$output = $output . "\n    <td> " . $rowVals[$i] . " </td>";
			}
			$output .= "</tr>\n";
		}

		$output .= "</table>\n";
	} else {
		//$output .= "''\nDidn't find any NPCs that spawn in " . $zoneName . "!''";
	}
	
	// output items
	// ------------
	if ($db->numRows($items))
	{
		$output .= "\n\n'''Items''' - ''Found " . $db->numRows($items) . " items that drop in " . $zoneName . ":''";
		
		//table header
		$output .= "\n<table class='eoTable3 sortable' style='width:100%;'>";
		$output .= "\n<tr><th>Item Name</th>";
		$output .=       "<th>Drops From</th>" . 
										 "<th>Slot</th>" . 	
										 "<th>Stats</th>" . 						 
										 "</tr>";
	 
		foreach ( $items as $row )
		{
			$title = Title::makeTitle( $row->page_namespace, $row->page_title);
			$titleText = $title->getText();

			// get quest header block
			$tTitle = Title::newFromText( $titleText );
			$article = new Article($tTitle);
			$templateText = $article->getContent();
			//$templateText = preg_replace( '/<!--.*?-->/s', '', $parser->fetchTemplate( $tTitle ) );

			if (strpos($templateText,"{{Velious Era}}") !== false)
			  continue;
				
			$rowVals = DynamicZoneList::parseItemPage($templateText,$zoneName);
			
			// output row
			$output = $output . "\n<tr><td> {{:" . $titleText . "}}\n</td>";
			for ($i=0; $i < count($rowVals); $i++)
			{
				$output = $output . "\n    <td> " . $rowVals[$i] . " </td>";
			}
			$output .= "</tr>\n";
		}

		$output .= "</table>\n";
	} else {
		//$output .= "\n''Didn't find any items that drop in " . $zoneName . "!''";
	}	
	
	// output footer
  $output .= "";
	
	// fix UNIQ problem
  $pOptions = new ParserOptions();
  $pOptions->initialiseFromUser($wgUser);
  $result = $parser->parse($output, $wgTitle, $pOptions);
  $wgOut->addHTML($result->getText());	
	
	//$wgOut->addWikiText($output);

}

}
