<?php
      
/*************************************************

p99wiki - extensions (ClassSlotEquip)
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
			
class ClassSlotEquip extends SpecialPage {

function __construct()
{
  parent::__construct( "ClassSlotEquip" );
  //parent::__construct("ClassSlotEquip","",true,false,'default',true); //to allow transclusion of this special page, ($name,$restriction,$listed,$function,$file,$includable);
}

function parseStatsBlock($statsBlock, $dropsFrom, $playerCrafted, $questOrigin, $purchasedFrom, $slot)
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

  // order corresponds to table column order
  $vals = array();

  // Damage/Delay (weapons only)
  if ( in_array($slot,$weapSlots) ) {

    if (preg_match('/DMG:\s+\d+/', $statsBlock, $regMatch) ) {
          $t1 = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
    } else { $t1 = ''; }

    if (preg_match('/Atk Delay:\s+\d+/', $statsBlock, $regMatch) ) {
          $t2 = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
    } else { $t2 = ''; }
    
    if ($t1 && $t2) {
      $vals[] = "<span class='ddb'>(" . sprintf("%4.2f",$t1/$t2) . ") </span> " . $t1 . " / " . $t2;
    } else {
      $vals[] = '';
    }
  }

  // Drops From (Zone)
  if ( strpos($dropsFrom, "]]") ) {
    $vals[] = trim(substr($dropsFrom,strpos($dropsFrom,"[["),strpos($dropsFrom, "]]")+2));
  } else {
    // note from tradeskill, quest, or other
    if      ( $playerCrafted != "" ) { $vals[] = $playerCrafted; }
    else if ( $questOrigin != "" )   { $vals[] = $questOrigin;   }
    else if ( $purchasedFrom != "" ) { $vals[] = $purchasedFrom; }
    else                             { $vals[] = '';             }
  }

  // AC
  if (preg_match('/AC:\s+\d+/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  // WT
  if (preg_match('/WT:\s+\d+.\d/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  // STR
  if (preg_match('/STR:\s+[+|-]\d+/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  // STA
  if (preg_match('/STA:\s+[+|-]\d+/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  // AGI
  if (preg_match('/AGI:\s+[+|-]\d+/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  // DEX
  if (preg_match('/DEX:\s+[+|-]\d+/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  // CHA
  if (preg_match('/CHA:\s+[+|-]\d+/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  // INT
  if (preg_match('/INT:\s+[+|-]\d+/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  // WIS
  if (preg_match('/WIS:\s+[+|-]\d+/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  // HP, MANA
  if (preg_match('/HP:\s+[+|-]\d+/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  if (preg_match('/MANA:\s+[+|-]\d+/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  // MR, FR, CR, PR, DR
  if (preg_match('/SV MAGIC:\s+[+|-]\d+/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  if (preg_match('/SV FIRE:\s+[+|-]\d+/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  if (preg_match('/SV COLD:\s+[+|-]\d+/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  if (preg_match('/SV POISON:\s+[+|-]\d+/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  if (preg_match('/SV DISEASE:\s+[+|-]\d+/', $statsBlock, $regMatch) ) {
        $vals[] = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
  } else { $vals[] = ''; }

  // Deity, Effect

  // Lore/NoDrop/Magic/Size



  return $vals;
}

function parseTemplateParameters($templateText)
{
  // subselect
  if ( strpos($templateText,"{{Itempage") ) {
    $templateText = substr($templateText,strpos($templateText,"{{Itempage")+2);
  }
  if ( strpos($templateText,"}}") ) {
    $templateText = substr($templateText,0,strpos($templateText,"}}")+2);
  }

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

    if ( $cbrackets == 2 && $c == '|' ) {
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

function execute( $par )
{
  global $wgRequest, $wgOut, $wgUser;
    
  $classNames = array('BRD' => 'Bard',
                      'CLR' => 'Cleric',
                      'DRU' => 'Druid',
                      'ENC' => 'Enchanter',
                      'MAG' => 'Magician',
                      'MNK' => 'Monk',
                      'NEC' => 'Necromancer',
                      'PAL' => 'Paladin',
                      'RNG' => 'Ranger',
                      'ROG' => 'Rogue',
                      'SHM' => 'Shaman',
                      'SHD' => 'Shadow Knight',
                      'WAR' => 'Warrior',
                      'WIZ' => 'Wizard');
                      
  $slotNames = array('ARMS'      => 'Arms',
                     'BACK'      => 'Back',
                     'CHEST'     => 'Chest',
                     'EAR'       => 'Ear',
                     'FACE'      => 'Face',
                     'FEET'      => 'Feet',
                     'FINGER'    => 'Fingers',
                     'HANDS'     => 'Hands',
                     'HEAD'      => 'Head',
                     'LEGS'      => 'Legs',
                     'NECK'      => 'Neck',
                     'SHOULDERS' => 'Shoulders',
                     'WAIST'     => 'Waist',
                     'WRIST'     => 'Wrist',
                     'PRIMARY'   => 'Primary',
                     'SECONDARY' => 'Secondary',
                     'RANGE'     => 'Range',
                     'AMMO'      => 'Ammo',
                     'INST'      => 'Bard Instrument');  
  
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

  // parse class & slot
  list($class,$slot,$era) = split("/",$par,3);
  $class = str_replace("_"," ",$class);
  $slot  = str_replace("_"," ",$slot);
  $era   = str_replace("_"," ",$era);
  
  if ( !$class || !$slot || !in_array($class,$classNames) || 
         (!in_array($slot,$slotNames) && !in_array($slot,$weapSlots)) ) {
    $wgOut->setPagetitle("Class Slot Equipment List");
    $wgOut->addWikiText("Error! Didn't recognize the class/slot combination, sorry.");
    return;
  }
  
  if ( $era != "" && $era != "PreVelOnly" && $era != "VelOnly" && $era != "AllItems" ) {
    $wgOut->setPagetitle("Class Slot Equipment List");
    $wgOut->addWikiText("Error! Didn't recognize the era selection, sorry.");
    return;
  }
  
  $catNames = array($class." Equipment",$slot);
  $iCatCount = 2;
  
  $parser = new Parser;
  $poptions = new ParserOptions;
  
  foreach ($catNames as $catName)
  {
  	$title = Title::newFromText( $parser->transformMsg($catName, $poptions) );
  	if( is_null( $title ) ) {
      $wgOut->addWikiText("Error! 8602");
      return;
  	}
  	$aCategories[] = $title;
  }

  // set headers
  $this->setHeaders();
  $wgOut->setPagetitle("$class :: $slot");
  
  // query
  $db = wfGetDB( DB_SLAVE );
  
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
	
	if ( $db->numRows( $res ) == 0 ) {
		$wgOut->addWikiText("No items found.");
    return;
	}
  
  // header info for class/slot
  $wgOut->addWikiText("Showing all [[:Category:$class Equipment|$class Equipment]]" . 
            " for the [[:Category:$slot|$slot]] slot (" . $db->numRows($res) . " items found).");
  
  // header info for era selection
  if ( $era == "" || $era == "PreVelOnly" )
    $wgOut->addWikiText("Currently listing '''pre-Velious items only.''' " . 
                        " (switch to [[Special:ClassSlotEquip/$class/$slot/VelOnly|Velious Only]] or [[Special:ClassSlotEquip/$class/$slot/AllItems|all eras]]?)");
  else if ( $era == "VelOnly" )
    $wgOut->addWikiText("Currently listing '''Velious era items only.'''" . 
                        " (switch to [[Special:ClassSlotEquip/$class/$slot/PreVelOnly|pre-Velious Only]] or [[Special:ClassSlotEquip/$class/$slot/AllItems|all eras]]?)");
  else if ( $era == "AllItems" )
    $wgOut->addWikiText("Currently listing '''items of all eras.'''" . 
                        " (switch to [[Special:ClassSlotEquip/$class/$slot/PreVelOnly|pre-Velious Only]] or [[Special:ClassSlotEquip/$class/$slot/VelOnly|Velious Only]]?)");
    
  // parse statblock from each page
  
  // format output
  $sk = $wgUser->getSkin();
  $query = array();
  $aLinkOptions = array();
  
  //table header
  $output .= "\n<table class='eoTable sortable'>";
  $output .= "\n<tr><th>Name</th>";

  if ( in_array($slot,$weapSlots) ) { $output .= "<th>Dmg/Delay</th>"; }

  $output .=       "<th>Drops From</th>";
  $output .=       "<th>AC</th>" . 
                   "<th>WT</th>" . 
                   "<th>STR</th>" . 
                   "<th>STA</th>" . 
                   "<th>AGI</th>" . 
                   "<th>DEX</th>" . 
                   "<th>CHA</th>" . 
                   "<th>INT</th>" . 
                   "<th>WIS</th>" . 
                   "<th>HP</th>" . 
                   "<th>MANA</th>" . 
                   "<th>MR</th>" . 
                   "<th>FR</th>" . 
                   "<th>CR</th>" . 
                   "<th>PR</th>" . 
                   "<th>DR</th>" . 
                   "</tr>";
 
  foreach ( $res as $row )
  {
    $title = Title::makeTitle( $row->page_namespace, $row->page_title);
    $titleText = $title->getText();

    // get statsblock
    $tTitle = Title::newFromText( $titleText );
    $article = new Article($tTitle);
    $templateText = $article->getContent();
    //$templateText = preg_replace( '/<!--.*?-->/s', '', $parser->fetchTemplate( $tTitle ) );

    // era selection
    if ( $era == "" || $era == "PreVelOnly" )
    {
	if (strpos($templateText,"{{Velious Era}}") !== false ||
	    strpos($templateText,"{{WarrensFearHateRevamp Era}}") !== false ||
	    strpos($templateText,"{{StonebruntChardokRevamp Era}}") !== false)
		continue;	
    }
    else if ( $era == "VelOnly" )
    {
	if (strpos($templateText,"{{Velious Era}}") === false &&
	    strpos($templateText,"{{WarrensFearHateRevamp Era}}") === false &&
	    strpos($templateText,"{{StonebruntChardokRevamp Era}}") === false)
		continue;
    }
		
    // start parse		
    $statsBlock = '';
    $dropsFrom  = '';
    $playerCrafted = '';
    $questOrigin   = '';
    $purchasedFrom = '';

    $parms = ClassSlotEquip::parseTemplateParameters($templateText);

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
    
    // alternate ways to obtain
    if ( strpos($templateText,"|playercrafted") )
      $playerCrafted = "Player Crafted";
    if ( strpos($templateText,"|relatedquests") )
      $questOrigin = "Quested";
    if ( strpos($templateText,"|soldby") )
      $purchasedFrom = "Purchased";

    // parse statsblock
    $rowVals = ClassSlotEquip::parseStatsBlock($statsBlock, $dropsFrom, $playerCrafted, $questOrigin, $purchasedFrom, $slot);

    // output row

    $output = $output . "\n<tr><td> {{:" . $titleText . "}}\n</td>";
    for ($i=0; $i < count($rowVals); $i++)
    {
      $output = $output . "\n    <td> " . $rowVals[$i] . " </td>";
    }
    $output .= "</tr>\n";
  }

  $output .= "</table>\n";

  //old ul output
	//$output .= "\n<ul><li>";
	//$output .= implode( "</li> \n <li>", $articleList ); 
	//$output .= "</li>";
	//$output .= "</ul>" . "\n";

  // output  
  //$wgOut->addHTML( $output );
  $wgOut->addWikiText( $output );
}

}
