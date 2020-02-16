<?php
      
/*************************************************

p99wiki - extensions (Magelo)
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
			
class Magelo extends SpecialPage {

function __construct()
{
  parent::__construct( "Magelo","",true,false,'default',true );
}

function sandboxParse($wikiText)
{
	global $wgTitle, $wgUser;
	$myParser = new Parser();
	$myParserOptions = ParserOptions::newFromUser($wgUser);
	$result = $myParser->parse($wikiText, $wgTitle, $myParserOptions);
	return $result->getText();
}

function parseStatsBlock($statsBlock, $stats, $slotName)
{
  // Damage (primary only)
	if (preg_match('/DMG:\s+\d+/', $statsBlock, $regMatch) ) {
		$dmg = trim(substr($regMatch[0],strpos($regMatch[0],":")+1));
		if ($slotName == "Primary")
			$stats["primary_dmg"] = $dmg;
	}
	
	// check for Celestial Fists (modify primary damage)
	//if ($slotName == "Hands" && strpos($statsBlock,"Celestial Fists") !== false)
	//	$stats["primary_dmg"] = 9;

  // AC
  if ( preg_match('/AC:\s+\d+/', $statsBlock, $regMatch) )
		$stats["item_ac"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  // WT
  if ( preg_match('/WT:\s+\d+.\d/', $statsBlock, $regMatch) )
		$stats["weight_used"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  // STR
  if ( preg_match('/STR:\s+[+|-]\d+/', $statsBlock, $regMatch) )
		$stats["str"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  // STA
  if ( preg_match('/STA:\s+[+|-]\d+/', $statsBlock, $regMatch) )
		$stats["sta"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  // AGI
  if ( preg_match('/AGI:\s+[+|-]\d+/', $statsBlock, $regMatch) )
		$stats["agi"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  // DEX
  if ( preg_match('/DEX:\s+[+|-]\d+/', $statsBlock, $regMatch) )
		$stats["dex"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  // CHA
  if ( preg_match('/CHA:\s+[+|-]\d+/', $statsBlock, $regMatch) )
		$stats["cha"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  // INT
  if ( preg_match('/INT:\s+[+|-]\d+/', $statsBlock, $regMatch) )
		$stats["int"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  // WIS
  if ( preg_match('/WIS:\s+[+|-]\d+/', $statsBlock, $regMatch) )
		$stats["wis"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  // HP, MANA
  if ( preg_match('/HP:\s+[+|-]\d+/', $statsBlock, $regMatch) )
		$stats["item_hp"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  if ( preg_match('/MANA:\s+[+|-]\d+/', $statsBlock, $regMatch) )
		$stats["item_mana"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  // MR, FR, CR, PR, DR
  if ( preg_match('/SV MAGIC:\s+[+|-]\d+/', $statsBlock, $regMatch) )
		$stats["mr"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  if ( preg_match('/SV FIRE:\s+[+|-]\d+/', $statsBlock, $regMatch) )
		$stats["fr"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  if ( preg_match('/SV COLD:\s+[+|-]\d+/', $statsBlock, $regMatch) )
		$stats["cr"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  if ( preg_match('/SV POISON:\s+[+|-]\d+/', $statsBlock, $regMatch) )
		$stats["pr"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  if ( preg_match('/SV DISEASE:\s+[+|-]\d+/', $statsBlock, $regMatch) )
		$stats["dr"] += trim(substr($regMatch[0],strpos($regMatch[0],":")+1));

  // Deity, Effect

  // Lore/NoDrop/Magic/Size

  return $stats;
}

/* --- stat calculation (EQEmu client_mods.cpp) --- */

function get_acmod($level, $agility)
{
	if($agility < 1 || $level < 1)
		return 0;

	if ($agility <=74)
	{
		if ($agility == 1)
			return -24;
		else if ($agility <=3)
			return -23;
		else if ($agility == 4)
			return -22;
		else if ($agility <=6)
			return -21;
		else if ($agility <=8)
			return -20;
		else if ($agility == 9)
			return -19;
		else if ($agility <=11)
			return -18;
		else if ($agility == 12)
			return -17;
		else if ($agility <=14)
			return -16;
		else if ($agility <=16)
			return -15;
		else if ($agility == 17)
			return -14;
		else if ($agility <=19)
			return -13;
		else if ($agility == 20)
			return -12;
		else if ($agility <=22)
			return -11;
		else if ($agility <=24)
			return -10;
		else if ($agility == 25)
			return -9;
		else if ($agility <=27)
			return -8;
		else if ($agility == 28)
			return -7;
		else if ($agility <=30)
			return -6;
		else if ($agility <=32)
			return -5;
		else if ($agility == 33)
			return -4;
		else if ($agility <=35)
			return -3;
		else if ($agility == 36)
			return -2;
		else if ($agility <=38)
			return -1;
		else if ($agility <=65)
			return 0;
		else if ($agility <=70)
			return 1;
		else if ($agility <=74)
			return 5;
	}
	else if($agility <= 137)
	{
		if ($agility == 75)
		{
			if ($level <= 6)
				return 9;
			else if ($level <= 19)
				return 23;
			else if ($level <= 39)
				return 33;
			else
				return 39;
		}
		else if ($agility >= 76 && $agility <= 79)
		{
			if ($level <= 6)
				return 10;
			else if ($level <= 19)
				return 23;
			else if ($level <= 39)
				return 33;
			else
				return 40;
		}
		else if ($agility == 80)
		{
			if ($level <= 6)
				return 11;
			else if ($level <= 19)
				return 24;
			else if ($level <= 39)
				return 34;
			else
				return 41;
		}
		else if ($agility >= 81 && $agility <= 85)
		{
			if ($level <= 6)
				return 12;
			else if ($level <= 19)
				return 25;
			else if ($level <= 39)
				return 35;
			else
				return 42;
		 }
		else if ($agility >= 86 && $agility <= 90)
		{
			if ($level <= 6)
				return 12;
			else if ($level <= 19)
				return 26;
			else if ($level <= 39)
				return 36;
			else
				return 42;
		 }
		else if ($agility >= 91 && $agility <= 95)
		{
			if ($level <= 6)
				return 13;
			else if ($level <= 19)
				return 26;
			else if ($level <= 39)
				return 36;
			else
				return 43;
		}
		else if ($agility >= 96 && $agility <= 99)
		{
			if ($level <= 6)
				return 14;
			else if ($level <= 19)
				return 27;
			else if ($level <= 39)
				return 37;
			else 
				return 44;
		}
		else if ($agility == 100 && $level >= 7)
		{
			if ($level <= 19)
				return 28;
			else if ($level <= 39)
				return 38;
			else
				return 45;
		}
		else if ($level <= 6)
		{
			return 15;
		}
		//level is >6
		else if ($agility >= 101 && $agility <= 105)
		{
			if ($level <= 19)
				return 29;
			else if ($level <= 39)
				return 39;# not verified
			else
				return 45;
		}
		else if ($agility >= 106 && $agility <= 110)
		{
			if ($level <= 19)
				return 29;
			else if ($level <= 39)
				return 39;# not verified
			else
				return 46;
		}
		else if ($agility >= 111 && $agility <= 115)
		{
			if ($level <= 19)
				return 30;
			else if ($level <= 39)
				return 40;# not verified
			else
				return 47;
		}
		else if ($agility >= 116 && $agility <= 119)
		{
			if ($level <= 19)
				return 31;
			else if ($level <= 39)
				return 41;
			else
				return 47;
		}
		else if ($level <= 19)
		{
				return 32;
		}
		//level is > 19
		else if ($agility == 120)
		{
			if ($level <= 39)
				return 42;
			else
				return 48;
		}
		else if ($agility <= 125)
		{
			if ($level <= 39)
				return 42;
			else
				return 49;
		}
		else if ($agility <= 135)
		{
			if ($level <= 39)
				return 42;
			else
				return 50;
		}
		else
		{
			if ($level <= 39)
				return 42;
			else
				return 51;
		}
	}
	else if($agility <= 300)
	{
		if($level <= 6)
		{
			if($agility <= 139)
				return(21);
			else if($agility == 140)
				return(22);
			else if($agility <= 145)
				return(23);
			else if($agility <= 150)
				return(23);
			else if($agility <= 155)
				return(24);
			else if($agility <= 159)
				return(25);
			else if($agility == 160)
				return(26);
			else if($agility <= 165)
				return(26);
			else if($agility <= 170)
				return(27);
			else if($agility <= 175)
				return(28);
			else if($agility <= 179)
				return(28);
			else if($agility == 180)
				return(29);
			else if($agility <= 185)
				return(30);
			else if($agility <= 190)
				return(31);
			else if($agility <= 195)
				return(31);
			else if($agility <= 199)
				return(32);
			else if($agility <= 219)
				return(33);
			else if($agility <= 239)
				return(34);
			else
				return(35);
		}
		else if($level <= 19) 
		{
			if($agility <= 139)
				return(34);
			else if($agility == 140)
				return(35);
			else if($agility <= 145)
				return(36);
			else if($agility <= 150)
				return(37);
			else if($agility <= 155)
				return(37);
			else if($agility <= 159)
				return(38);
			else if($agility == 160)
				return(39);
			else if($agility <= 165)
				return(40);
			else if($agility <= 170)
				return(40);
			else if($agility <= 175)
				return(41);
			else if($agility <= 179)
				return(42);
			else if($agility == 180)
				return(43);
			else if($agility <= 185)
				return(43);
			else if($agility <= 190)
				return(44);
			else if($agility <= 195)
				return(45);
			else if($agility <= 199)
				return(45);
			else if($agility <= 219)
				return(46);
			else if($agility <= 239)
				return(47);
			else
				return(48);
		}
		else if($level <= 39)
		{
			if($agility <= 139)
				return(44);
			else if($agility == 140)
				return(45);
			else if($agility <= 145)
				return(46);
			else if($agility <= 150)
				return(47);
			else if($agility <= 155)
				return(47);
			else if($agility <= 159)
				return(48);
			else if($agility == 160)
				return(49);
			else if($agility <= 165)
				return(50);
			else if($agility <= 170)
				return(50);
			else if($agility <= 175)
				return(51);
			else if($agility <= 179)
				return(52);
			else if($agility == 180)
				return(53);
			else if($agility <= 185)
				return(53);
			else if($agility <= 190)
				return(54);
			else if($agility <= 195)
				return(55);
			else if($agility <= 199)
				return(55);
			else if($agility <= 219)
				return(56);
			else if($agility <= 239)
				return(57);
			else
				return(58);
		}
		else	//lvl >= 40
		{
			if($agility <= 139)
				return(51);
			else if($agility == 140)
				return(52);
			else if($agility <= 145)
				return(53);
			else if($agility <= 150)
				return(53);
			else if($agility <= 155)
				return(54);
			else if($agility <= 159)
				return(55);
			else if($agility == 160)
				return(56);
			else if($agility <= 165)
				return(56);
			else if($agility <= 170)
				return(57);
			else if($agility <= 175)
				return(58);
			else if($agility <= 179)
				return(58);
			else if($agility == 180)
				return(59);
			else if($agility <= 185)
				return(60);
			else if($agility <= 190)
				return(61);
			else if($agility <= 195)
				return(61);
			else if($agility <= 199)
				return(62);
			else if($agility <= 219)
				return(63);
			else if($agility <= 239)
				return(64);
			else
				return(65);
		}
	}
	else
	{
		//seems about 21 agil per extra AC pt over 300...
	  return (65 + (($agility-300) / 21));
  }
	
	return 0;
}

function get_class_level_factor($mlevel, $class)
{
  $multiplier = 0;
	
  switch ( $class )
	{
    case 'Warrior':
      if ($mlevel < 20)
				$multiplier = 220;
			else if ($mlevel < 30)
				$multiplier = 230;
			else if ($mlevel < 40)
				$multiplier = 250;
			else if ($mlevel < 53)
				$multiplier = 270;
			else if ($mlevel < 57)
				$multiplier = 280;
			else if ($mlevel < 60)
				$multiplier = 290;
			else if ($mlevel < 70)
				$multiplier = 300;
			else 
				$multiplier = 311;
		  break;
    case 'Druid':
		case 'Cleric':
		case 'Shaman':
			if ($mlevel < 70)
				$multiplier = 150;
			else
				$multiplier = 157;
		  break;
		case 'Paladin':
		case 'Shadow Knight':
		  if ($mlevel < 35)
				$multiplier = 210;
			else if ($mlevel < 45)
				$multiplier = 220;
			else if ($mlevel < 51)
				$multiplier = 230;
			else if ($mlevel < 56)
				$multiplier = 240;
			else if ($mlevel < 60)
				$multiplier = 250;
			else if ($mlevel < 68)
				$multiplier = 260;
			else
				$multiplier = 270;	
		  break;
		case 'Monk':
		case 'Bard':
		case 'Rogue':
		  if ($mlevel < 51)
				$multiplier = 180;
			else if ($mlevel < 58)
				$multiplier = 190;
			else if ($mlevel < 70)
				$multiplier = 200;
			else
				$multiplier = 210;
      break;
		case 'Ranger':
		  if ($mlevel < 58)
				$multiplier = 200;
			else if ($mlevel < 70)
				$multiplier = 210;
			else
				$multiplier = 220;
		  break;
		case 'Magician':
		case 'Wizard':
		case 'Necromancer':
		case 'Enchanter':
		  if ($mlevel < 70)
    		$multiplier = 120;
    	else
    		$multiplier = 127;
      break;
    default:
      if ($mlevel < 35)
				$multiplier = 210;
			else if ($mlevel < 45)
				$multiplier = 220;
			else if ($mlevel < 51)
				$multiplier = 230;
			else if ($mlevel < 56)
				$multiplier = 240;
			else if ($mlevel < 60)
				$multiplier = 250;
			else
				$multiplier = 260;	
			break;
  }
	
  return $multiplier;
}

function get_max_defense($class, $level)
{
  switch ($class) {
    case 'Wizard':
		case 'Magician':
		case 'Necromancer':
		case 'Enchanter':
      return round($level * 2.9);
    case 'Warrior':
		case 'Shadow Knight':
		case 'Paladin':
      return round($level * 4.2);
    case 'Rogue':
		case 'Bard':
		case 'Ranger':
		case 'Cleric':
		case 'Druid':
		case 'Shaman':
      return round($level * 4.0);
    case 'Monk':
      return round($level * 4.6);
  }
}

function melee_skill_cap($level, $class)
{
  switch ( $class )
	{
    case 'Cleric':
		case 'Druid':
      return round($level * 3.5);
    case 'Magician':
		case 'Necromancer':
		case 'Enchanter':
		case 'Wizard':
      return round($level * 2.2);
  }
	
  return round($level * 4);
}

function offense_skill_cap($level, $class)
{
  switch ( $class )
	{
    case 'Bard':
		case 'Druid':
		case 'Cleric':
      return round($level * 4);
    case 'Magician':
		case 'Necromancer':
		case 'Enchanter':
		case 'Wizard':
      return round($level * 2.8);
  }
	
	return round($level * 4.2);
}

function get_ac($acmod, $defense, $class, $item_ac, $level, $race)
{
  $avoidance = ($acmod + (($defense *  16)/9));

	if ($avoidance < 0)
		$avoidance = 0;
  
  $mitigation = 0;
	
  if ($class == 'Wizard' || $class == 'Magician' || 
	    $class == 'Necromancer' || $class = 'Enchanter')
	{
    $mitigation = $defense/4 + ($item_ac+1);
    $mitigation -= 4;
	}
  else
	{
    $mitigation = $defense/3 + (($item_ac*4)/3);
    if ($class == 'Monk')
      $mitigation += $level * 13/10;
  }

  $displayed = (($avoidance+$mitigation)*1000)/847;
  if ($race == 'Iksar')
	{
		$displayed += 12;
		$iksarlevel = $level;
		$iksarlevel -= 10;
		
		if ($iksarlevel > 25)
			$iksarlevel = 25;
			
		if ($iksarlevel > 0)
			$displayed += $iksarlevel * 12 / 10;
	}
	
	return $displayed;
}

/* ---- return stats ----- */

function get_total_attack($primary_dmg, $str, $item_attack, $level, $class)
{
  $offense = Magelo::offense_skill_cap($level, $class);
	
  if ($primary_dmg > 0)
    $skill = Magelo::melee_skill_cap($level, $class);
  else
    $skill = 0;

  if ($item_attack > 250)
    $item_attack = 250;

  $raiting = (($item_attack * 1.342) + ($offense * 1.345) + (($str - 66) * 0.9) + ($skill * 2.69));
	
  if ($raiting < 10)
    $raiting = 10;
	
  return round($raiting);
}

function get_total_ac($agi, $class, $item_ac, $level, $race)
{
	$acmod = Magelo::get_acmod($level, $agi);
	$max_defense = Magelo::get_max_defense($class, $level);
	
	$total_ac_before_mod = $item_ac + $acmod; // seems to disagree with variable name
	
  $total_ac = Magelo::get_ac($acmod,$max_defense,$class,$total_ac_before_mod,$level,$race);
	
	return round($total_ac);
}

function get_total_hp($level, $class, $sta, $item_hp)
{
  $lmod = Magelo::get_class_level_factor($level,$class);

  $post_255 = 0;
	
  if ($sta-255/2 > 0)
    $post_255 = ($sta-255/2);
  
  $base_hp = (5)+($level*$lmod/10) + ((($sta-$post_255)*$level*$lmod/3000)) + 
	       (($post_255*$level)*$lmod/6000);

	$nd = 10000;
	$max_hp = $base_hp + $item_hp;
	$max_hp = ($max_hp * $nd) / 10000;
	return round($max_hp);
}

function get_total_mana($item_mana, $level, $class, $int, $wis)
{
  $win_int = 0;
  $mind_lesser_factor = 0;
	$mind_facotor = 0;
	$max_mana = 0;
	
  switch ( $class )
	{
    case 'Magician':
		case 'Necromancer':
		case 'Enchanter':
		case 'Wizard':
		case 'Bard':
		case 'Shadow Knight':
      if((( $int - 199 ) / 2) > 0)
				$mind_lesser_factor = ( $int - 199 ) / 2;
			else
			  $mind_lesser_factor = 0;

			$mind_factor = $int - $mind_lesser_factor;
			
			if ($int > 100)
				$max_mana = (((5 * ($mind_factor + 20)) / 2) * 3 * $level / 40);
			else
				$max_mana = (((5 * ($mind_factor + 200)) / 2) * 3 * $level / 100);
			
			break;
    case 'Druid':
		case 'Cleric':
		case 'Shaman':
		case 'Paladin':
		case 'Ranger':
      	if((($wis - 199 ) / 2) > 0)
  				$mind_lesser_factor = ( $wis - 199 ) / 2;
  			else
  				$mind_lesser_factor = 0;

  			$mind_factor = $wis - $mind_lesser_factor;
				
  			if ($wis > 100)
  				$max_mana = (((5 * ($mind_factor + 20)) / 2) * 3 * $level / 40);
  			else
  				$max_mana = (((5 * ($mind_factor + 200)) / 2) * 3 * $level / 100);

				break;
    default:
      return 0;
  }
  
  return round($max_mana + $item_mana);
}

function getBaseResists($stats,$race,$class,$level)
{
	$stats["mr"] = 25;
	$stats["fr"] = 25;
	$stats["cr"] = 25;
	$stats["dr"] = 15;
	$stats["pr"] = 15;
	
// unsure if these is implemented:
/*
	if(GetClass() == WARRIOR)
		MR += GetLevel() / 2;	
	
	if(c == RANGER) {
		FR += 4;
		CR += 4;

		int l = GetLevel();
		if(l > 49) {
			FR += l - 49;
			CR += l - 49;
		}
	}	
	
	if(c == PALADIN) {
		DR += 8;

		int l = GetLevel();
		if(l > 49)
			DR += l - 49;

	} else if(c == SHADOWKNIGHT) {
		DR += 4;
		PR += 4;

		int l = GetLevel();
		if(l > 49) {
			DR += l - 49;
			PR += l - 49;
		}
	}	
	
	if(c == ROGUE) {
		PR += 8;

		int l = GetLevel();
		if(l > 49)
			PR += l - 49;

	}
*/
	
	switch ( $race )
	{
		case 'Barbarian':
			$stats["cr"] = 35;
			break;
		case 'Erudite':
			$stats["mr"] = 30;
			$stats["dr"] = 10;
			break;
		case 'Dwarf':
			$stats["mr"] = 30;
			$stats["pr"] = 20;
			break;
		case 'Troll':
			$stats["fr"] = 5;
			break;
		case 'Halfling':
			$stats["dr"] = 20;
			$stats["pr"] = 20;
			break;
		case 'Iksar':
			$stats["fr"] = 30;
			$stats["cr"] = 15;
			break;
	}

	// apply the monk primary damage here based on level, will be overridden with any items
	if ( $class == "Monk" ) {
		if ($level >= 1)  $stats["primary_dmg"] = 4;
		if ($level >= 5)  $stats["primary_dmg"] = 5;
		if ($level >= 10) $stats["primary_dmg"] = 6;
		if ($level >= 15) $stats["primary_dmg"] = 7;
		if ($level >= 20) $stats["primary_dmg"] = 8;
		if ($level >= 25) $stats["primary_dmg"] = 9;
		if ($level >= 30) $stats["primary_dmg"] = 10;
		if ($level >= 35) $stats["primary_dmg"] = 11;
		if ($level >= 40) $stats["primary_dmg"] = 12;
		if ($level >= 45) $stats["primary_dmg"] = 13;
		if ($level >= 50) $stats["primary_dmg"] = 14;		
	}
	
	return $stats;
}

function getBaseStats($stats,$text)
{
	$ret = preg_match("/BaseSTR:(.*?)\n/",$text,$match);
	if ($ret && strlen(trim($match[1]))) $stats["str"] = trim($match[1]);
	$ret = preg_match("/BaseSTA:(.*?)\n/",$text,$match);
	if ($ret && strlen(trim($match[1]))) $stats["sta"] = trim($match[1]);
	$ret = preg_match("/BaseAGI:(.*?)\n/",$text,$match);
	if ($ret && strlen(trim($match[1]))) $stats["agi"] = trim($match[1]);
	$ret = preg_match("/BaseDEX:(.*?)\n/",$text,$match);
	if ($ret && strlen(trim($match[1]))) $stats["dex"] = trim($match[1]);
	$ret = preg_match("/BaseWIS:(.*?)\n/",$text,$match);
	if ($ret && strlen(trim($match[1]))) $stats["wis"] = trim($match[1]);
	$ret = preg_match("/BaseINT:(.*?)\n/",$text,$match);
	if ($ret && strlen(trim($match[1]))) $stats["int"] = trim($match[1]);
	$ret = preg_match("/BaseCHA:(.*?)\n/",$text,$match);
	if ($ret && strlen(trim($match[1]))) $stats["cha"] = trim($match[1]);

	if ($stats["str"] < 0 || $stats["str"] > 200) $stats["str"] = 0;
	if ($stats["sta"] < 0 || $stats["sta"] > 200) $stats["sta"] = 0;
	if ($stats["agi"] < 0 || $stats["agi"] > 200) $stats["agi"] = 0;
	if ($stats["dex"] < 0 || $stats["dex"] > 200) $stats["dex"] = 0;	
	if ($stats["wis"] < 0 || $stats["wis"] > 200) $stats["wis"] = 0;
	if ($stats["int"] < 0 || $stats["int"] > 200) $stats["int"] = 0;
	if ($stats["cha"] < 0 || $stats["cha"] > 200) $stats["cha"] = 0;
	
	return $stats;
}

function finalStatsChecks($stats,$class,$level)
{
	$maxStat = 255; // regardless of level in classic
	
	if ( $stats["str"] > $maxStat ) $stats["str"] = $maxStat;
	if ( $stats["sta"] > $maxStat ) $stats["sta"] = $maxStat;
	if ( $stats["agi"] > $maxStat ) $stats["agi"] = $maxStat;
	if ( $stats["dex"] > $maxStat ) $stats["dex"] = $maxStat;
	if ( $stats["wis"] > $maxStat ) $stats["wis"] = $maxStat;
	if ( $stats["int"] > $maxStat ) $stats["int"] = $maxStat;
	if ( $stats["cha"] > $maxStat ) $stats["cha"] = $maxStat;	
	
	return $stats;
}

/* ------- hooks --------- */

function AlternateEdit( $editpage )
{
	global $wgOut, $wgUser, $wgRequest;

	if (  (!in_array( 'sysop', $wgUser->getGroups() )) && ($editpage->mArticle->mTitle->getNamespace() == NS_MAGELO_BLUE || $editpage->mArticle->mTitle->getNamespace() == NS_MAGELO_RED) && (!$editpage->preview) )
	{
			$dbw = wfGetDB( DB_SLAVE );

			// title of the page
			$titre_page = $editpage->mArticle->mTitle->getText();
			$titre_page = str_replace(" ","_", $titre_page);

			$table_1 = $dbw->tableName( 'page' );
			$table_2 = $dbw->tableName( 'revision' );

			// page's id
			$res1 = $dbw->query("SELECT page_id FROM $table_1 WHERE page_title=\"$titre_page\";");
			foreach ( $res1 as $row )
			  $id_page = $row->page_id;
				
			// useName
			$res2 = $dbw->query("SELECT rev_user_text FROM $table_2 WHERE rev_page = \"$id_page\" LIMIT 1;");
			foreach ( $res2 as $row )
			  $nom_user = $row->rev_user_text;

			// --- check the UserName
			if($wgUser->getName() != $nom_user && $nom_user != null) {
					$wgOut->addWikiText("'''Note:''' Only the original creator of a Magelo page can make edits. If there is a problem or a character name conflict, leave a note [[User_talk:Ravhin|here]].\n\n");
					$wgOut->readOnlyPage ($editpage->mArticle->getContent(true), true);
							return false;
					}

	}

	// default behavior - edit allowed, proceed normally
	return true;
}

function PreloadText( &$text, &$title )
{
		if( $title->getNamespace() == NS_MAGELO_BLUE || $title->getNamespace() == NS_MAGELO_RED )
		{
				$text = <<<EOT
startMageloProfile

* Name: 
* Class: 
* Race: 
* Level: 
* Guild: 
* Religion: 

* BaseSTR: 
* BaseSTA: 
* BaseAGI: 
* BaseDEX: 
* BaseWIS: 
* BaseINT: 
* BaseCHA: 

* Neck: 
* Head: 
* Ears1: 
* Ears2: 
* Face: 
* Chest: 
* Arms: 
* Back: 
* Waist: 
* Shoulders: 
* Wrists1: 
* Wrists2: 
* Legs: 
* Hands: 
* Fingers1: 
* Fingers2: 
* Feet: 

* Primary: 
* Secondary: 
* Range: 
* Ammo: 

* Inv1: 
* Inv2: 
* Inv3: 
* Inv4: 
* Inv5: 
* Inv6: 
* Inv7: 
* Inv8: 

endMageloProfile

== Extra ==

You can delete this section, or add anything you'd like here (wiki syntax).
	
EOT;
		
		}
		
		return true;
}

function onParserAfterTidy( &$parser, &$text )
{
	global $wgOut, $wgUser, $wgRequest, $wgTitle, $action;
	$pOptions = new ParserOptions();
	
	// config
	$link_prefix = "/"; //"/index.php/";
	$startTag = "startMageloProfile";
	$endTag = "endMageloProfile";	
	
	// set restricted clsas,race,religion choices and nicknames
	$classes = 		array('brd' => 'Bard',       'bard'      => 'Bard',
                      'clr' => 'Cleric',     'cle'       => 'Cleric',   'cleric'    => 'Cleric',
                      'dru' => 'Druid',      'druid'     => 'Druid',
                      'enc' => 'Enchanter',  'chanter'   => 'Enchanter','enchanter' => 'Enchanter',
                      'mag' => 'Magician',   'mage'      => 'Magician', 'magician'  => 'Magician',
                      'mnk' => 'Monk',       'monk'      => 'Monk',
                      'nec' => 'Necromancer','necro'     => 'Necromancer', 'necromancer' => 'Necromancer',
                      'pal' => 'Paladin',    'pally'     => 'Paladin',     'paladin'     => 'Paladin',
                      'rng' => 'Ranger',     'ranger'    => 'Ranger',
                      'rog' => 'Rogue',      'rogue'     => 'Rogue',
                      'shm' => 'Shaman',     'shammy'    => 'Shaman',      'shaman'      => 'Shaman',
                      'shd' => 'Shadow Knight','sk'      => 'Shadow Knight','shadowknight' => 'Shadow Knight','shadow knight' => 'Shadow Knight',
                      'war' => 'Warrior',    'warrior'   => 'Warrior',
                      'wiz' => 'Wizard',     'wizzy'     => 'Wizard',       'wizard' => 'Wizard');
											
	$races = array('human'     => 'Human',
								 'barbarian' => 'Barbarian','bar' => 'Barbarian',
								 'erudite'   => 'Erudite',  'eru' => 'Erudite',
								 'wood elf'  => 'Wood Elf', 'woodelf' => 'Wood Elf',
								 'high elf'  => 'High Elf', 'highelf' => 'High Elf',
								 'dark elf'  => 'Dark Elf', 'darkelf' => 'Dark Elf',
								 'half elf'  => 'Half Elf', 'halfelf' => 'Half Elf',
								 'dwarf'     => 'Dwarf',    'dorf'    => 'Dwarf',
								 'troll'     => 'Troll',    'trl'     => 'Troll',
								 'ogre'      => 'Ogre',     'fatty'   => 'Ogre',
								 'halfling'  => 'Halfling',
	               'gnome'     => 'Gnome',
								 'iksar'     => 'Iksar',    'lizard'  => 'Iksar');
								 
	$religions = array('bertoxxulous'   => 'Bertoxxulous',  'bertox'      => 'Bertoxxulous',
										 'brell serilis'  => 'Brell Serilis', 'brell'       => 'Brell Serilis',
										 'bristlebane'    => 'Bristlebane',
										 'cazic thule'    => 'Cazic Thule',   'cazic-thule' => 'Cazic Thule',
										 'erollisi marr'  => 'Erollisi Marr', 'erollisi'    => 'Erollisi Marr',
										 'innoruuk'       => 'Innoruuk',      'inny'        => 'Innoruuk',
										 'karana'         => 'Karana',
										 'mithaniel marr' => 'Mithaniel Marr', 'mithaniel'  => 'Mithaniel Marr',
										 'prexus'         => 'Prexus',
										 'quellious'      => 'Quellious',
										 'rallos zek'     => 'Rallos Zek',     'rallos'     => 'Rallos Zek',
										 'rodcet nife'    => 'Rodcet Nife',    'rodcet'     => 'Rodcet Nife',
										 'solusek ro'     => 'Solusek Ro',     'solusek'    => 'Solusek Ro',
										 'the tribunal'   => 'The Tribunal',   'tribunal'   => 'The Tribunal',
										 'tunare'         => 'Tunare',
										 'veeshan'        => 'Veeshan',
										 'agnostic'       => 'Agnostic');
	
	// slot number -> name mapping
	$slotNames = array(1  => 'Ears1',    2 => 'Head',     3 => 'Face', 4 => 'Ears2',
	                   17 => 'Chest',    5 => 'Neck', 
										 7  => 'Arms',     8 => 'Back',
										 20 => 'Waist',    6 => 'Shoulders',
										 9  => 'Wrists1',  10 => 'Wrists2',
										 18 => 'Legs',     12 => 'Hands', /*0 => 'Charm',*/ 19 => 'Feet',
										 15 => 'Fingers1', 16 => 'Fingers2',
										 13 => 'Primary',  14 => 'Secondary', 11 => 'Range', 21 => 'Ammo',
										 22 => 'Inv1',     23 => 'Inv2',      24 => 'Inv3',  25 => 'Inv4',
										 26 => 'Inv5',     27 => 'Inv6',      28 => 'Inv7',  29 => 'Inv8');

	// exit if we're not parsing the right kind of page
	$startPos = strpos( $text, $startTag );	
	
	if ( ($parser->mTitle->getNamespace() != NS_MAGELO_BLUE && $parser->mTitle->getNamespace() != NS_MAGELO_RED) || $startPos === false) {
		return true;
	}	
	
	// parse mageloProfile html and set all variables
	$name = "Name?";
	$ret = preg_match("/Name:(.*?)\n/",$text,$match);
	if ($ret && strlen(trim($match[1]))) $name = trim($match[1]);
	if (strlen($name) > 40)
		$name = substr($name,0,37) . "...";
	
	$class = "Class?";
	$ret = preg_match("/Class:(.*?)\n/",$text,$match);
	if ($ret && strlen(trim($match[1]))) {
		$trial = strtolower(trim($match[1]));
		if (array_key_exists($trial,$classes))
			$class = $classes[$trial];
	}
	
	$race = "Race?";
	$ret = preg_match("/Race:(.*?)\n/",$text,$match);
	if ($ret && strlen(trim($match[1]))) {
		$trial = strtolower(trim($match[1]));
		if (array_key_exists($trial,$races))
			$race = $races[$trial];
	}			
	
	$guild = "None";
	$ret = preg_match("/Guild:(.*?)\n/",$text,$match);
	if ($ret && strlen(trim($match[1]))) $guild = trim($match[1]);
	if (strlen($guild) < 2) {
	  $guild = "None";
	} else {
		// strip < > if present
		if (substr($guild,0,1) != "<" && substr($guild,0,5) != "&#60;")
			$guild = "&#60; " . $guild;
		if (substr($guild,-1) != ">" && substr($guild,-5) != "&#62;")
			$guild = $guild . " &#62;";
	}
	// insert a <br> in a space in the middle if the name is long
	if (strlen($guild) > 14) {
		$spacePos = strpos($guild," ");
		if ($spacePos < 6) $spacePos = strpos($guild," ",$spacePos+1);
		if ($spacePos > 6) $guild = substr($guild,0,$spacePos) . "<br>" . substr($guild,$spacePos);
	}
	
	$level = "Lvl?";
	$ret = preg_match("/Level:(.*?)\n/",$text,$match);
	if ($ret && is_numeric(trim($match[1]))) $level = intval(trim($match[1]));
	if ($level <= 0 || $level > 60) $level = "Lvl?";
	
	$religion = "Religion?";
	$ret = preg_match("/Religion:(.*?)\n/",$text,$match);
	if ($ret && strlen(trim($match[1]))) {
		$trial = strtolower(trim($match[1]));
		if (array_key_exists($trial,$religions))
			$religion = $religions[$trial];
	}			

	// get base stats
	$stats = array("hp"   => 0, "mana" => 0, "endr" => 0, "ac"   => 0, "atk"  => 0,
								 "str"  => 0,	"sta"  => 0, "agi"  => 0, "dex"  => 0, "wis"  => 0, "int"  => 0, "cha"  => 0,
								 "pr"   => 0,	"mr"   => 0, "dr"   => 0,	"fr"   => 0, "cr"   => 0,
								 "weight_used"  => 0.0,	"weight_total" => '?',
								 "item_hp"      => 0,	"item_mana"    => 0, "item_ac"      => 0,	"item_attack"  => 0,
								 "primary_dmg"  => 0);
								 
	$stats = Magelo::getBaseResists($stats,$race,$class,$level);
	$stats = Magelo::getBaseStats($stats,$text);
	
	// loop over each item slot, load page, accumulate stats and make html snippets
	$itemHTML = "";

	foreach ( $slotNames as $slot => $slotName )
	{
		$itemName = "";
		$templateText = "";
		$hoverWikiText = "";
		
		// add blank slot to html (equip slot or inventory slot)
		if ($slot < 22) {
			$itemHTML .= "<div class='Slot slotloc" . $slot . " slotimage" . $slot . "'></div>\n";
		} else {
			$itemHTML .= "<div class='Slot slotloc" . $slot . " slotimage'></div>\n";
		}
		// retrieve item from this slot from profile
		$ret = preg_match("/$slotName:(.*?)\n/",$text,$match);
		if (!$ret || !strlen(trim($match[1]))) continue;
		$itemName = trim($match[1]);
	
		// retrieve item page
		$tTitle = Title::newFromText( $itemName );
		$article = new Article($tTitle);
		$templateText = $article->getContent();
		
		// item is a redirect? follow
		if (strpos($templateText,"#REDIRECT") !== false) {
			$ret = preg_match("/#REDIRECT \[\[(.*?)\]\]/",$templateText,$match);
			if (!$ret) continue;
			
			$itemName = $match[1]; // new item name (alternative spelling usually)
			$tTitle = Title::newFromText( $itemName );
			$article2 = new Article($tTitle);
			$templateText = $article2->getContent();
		}		
		
		// item does not exist? skip
		if (strpos($templateText,"There is currently no text in this page.") !== false) continue;
		
		// parse item and accumulate stats (for non-inventory only)
		if ($slot < 22)
			$stats = Magelo::parseStatsBlock($templateText, $stats, $slotName);
		
		// get Item_###.png
		$ret = preg_match("/lucy_img_ID =(.*?)\n/",$templateText,$match);
		if ($ret) $imgNumber = trim($match[1]);
		
		// get hover section
		$ret = preg_match("/<span class=\"hb\">(.*?)<\/span><\/div>/s",$templateText,$match);
		if ($ret) $hoverWikiText = trim($match[1]);
		
		// cannot have <a> within <a>, remove any links inside statsblock (effect)
		$hoverWikiText = preg_replace("/\[\[(.*?)(\|+)(.*?)\]\]/","$3",$hoverWikiText); // [[link|link text]] format
		$hoverWikiText = preg_replace("/\[\[(.*?)\]\]/","$1",$hoverWikiText); // [[link]] format

		// use parser to convert the wikitext to html
		$hoverOut = $parser->parse($hoverWikiText,$wgTitle,$pOptions);
		$hoverHTML = "<span class=\"hb\">" . $hoverOut->getText() . "</span>";

		// add this slot to html
		$itemHTML .= "<a href=\"" . $link_prefix . str_replace(" ","_",$itemName) . "\"><div class='Slot slotloc" . $slot . " magelohb' style='background-image: url(../images/Item_" . $imgNumber . ".png);'>" . $hoverHTML . "</div></a>\n\n";
	}

	// construct final stats for class,race,level,items combo
	$stats["hp"] = Magelo::get_total_hp($level,$class,$stats["sta"],$stats["item_hp"]);
	$stats["mana"] = Magelo::get_total_mana($stats["item_mana"],$level,$class,$stats["int"],$stats["wis"]);
	$stats["ac"] = Magelo::get_total_ac($stats["agi"],$class,$stats["item_ac"],$level,$race);
	$stats["atk"] = Magelo::get_total_attack($stats["primary_dmg"], $stats["str"], $stats["item_attack"],$level,$class);
	$stats["weight_used"] = round($stats["weight_used"]);
	$stats["weight_total"] = $stats["str"];
	
	$stats = Magelo::finalStatsChecks($stats,$class,$level);
	
	$img_name = $class . ".gif";
	if ( $class == "Shadow Knight" )
		$img_name = "Shadowknight.gif";
	
	// create output html
	$output =  <<<EOT
	
<table>
  <tr>
    <td width='460px' align='center' valign='top'>
      <div class='IventoryOuter'>
			
				<div class='IventoryTitle'>
					<div class='IventoryTitleLeft'></div>
					<div class='IventoryTitleMid'><b> $name </b></div>
					<div class='IventoryTitleRight'></div>
				</div>
				
				<div class='InventoryInner'>
				
	  <div class='InventoryStats2'>
	    <table class='StatTable'>
	      <tr><td>Guild:</td></tr>
        <tr><td> $guild </td></tr>
	    </table>
	  </div>				
				
					<div class='InventoryStats'>
						<table class='StatTable'>
										<tr><td colspan='2'> $level $class <br> $race - <i>$religion</i></td></tr>
										<tr><td colspan='2' style='height: 6px'></td></tr>
										<tr>
											<td>HP <br>MANA<br>AC<br>ATK</td>
											<td width='100%'> {$stats["hp"]} <br> {$stats["mana"]} <br> {$stats["ac"]} <br> {$stats["atk"]} </td>
										</tr>
							<tr><td class='Divider' colspan='2'></td></tr>
							<tr>
								<td>STR<br>STA<br>AGI<br>DEX</td>
								<td width='100%'> {$stats["str"]} <br> {$stats["sta"]} <br> {$stats["agi"]} <br> {$stats["dex"]} </td>
										</tr>
										<tr><td class='Divider' colspan='2'></td></tr>
										<tr>
								<td>WIS<br>INT<br>CHA</td>
								<td width='100%'> {$stats["wis"]} <br> {$stats["int"]} <br> {$stats["cha"]} </td>
										</tr>
										<tr><td class='Divider' colspan='2'></td></tr>
										<tr>
								<td>POISON<br>MAGIC<br>DISEASE<br>FIRE<br>COLD</td>
								<td> {$stats["pr"]} <br> {$stats["mr"]} <br> {$stats["dr"]} <br> {$stats["fr"]} <br> {$stats["cr"]} </td>
										</tr>
										<tr><td class='Divider' colspan='2'></td></tr>
										<tr>
								<td>WEIGHT</td>
								<td> {$stats["weight_used"]} / {$stats["weight_total"]} </td>
							</tr>
						</table>
					</div>

					<div class='InventoryMonogram'><img src='./images/$img_name'></div>

					<div class='Coin' style='top: 106px;left: 317px;'><table class='StatTable'><tr><td align='left'><img src='../extensions/Magelo/images/pp.gif'></td><td align='center' width='100%'>-</td></tr></table></div>
					<div class='Coin' style='top: 134px;left: 317px;'><table class='StatTable'><tr><td align='left'><img src='../extensions/Magelo/images/gp.gif'></td><td align='center' width='100%'>-</td></tr></table></div>
					<div class='Coin' style='top: 162px;left: 317px;'><table class='StatTable'><tr><td align='left'><img src='../extensions/Magelo/images/sp.gif'></td><td align='center' width='100%'>-</td></tr></table></div>
					<div class='Coin' style='top: 190px;left: 317px;'><table class='StatTable'><tr><td align='left'><img src='../extensions/Magelo/images/cp.gif'></td><td align='center' width='100%'>-</td></tr></table></div>

					$itemHTML
					
				</div>
      </div>
    </td>
  </tr>
</table>

EOT;
	
	// add output to page
	$endPos = stripos($text, $endTag, $startPos);
	
	$text = substr($text,0,$startPos) . $output . substr($text,$endPos+strlen($endTag));
	
	return true;
}

function execute( $par )
{
  global $wgRequest, $wgOut, $wgUser;
	$options = explode("/",$par);
	
	$guildMaps = array("tmo" => "the mystical order",
	                   "bda" => "bregan d'aerth");
	
	// set restricted clsas,race,religion choices and nicknames
	$classes = 		array('brd' => 'Bard',       'bard'      => 'Bard',
                      'clr' => 'Cleric',     'cle'       => 'Cleric',   'cleric'    => 'Cleric',
                      'dru' => 'Druid',      'druid'     => 'Druid',
                      'enc' => 'Enchanter',  'chanter'   => 'Enchanter','enchanter' => 'Enchanter',
                      'mag' => 'Magician',   'mage'      => 'Magician', 'magician'  => 'Magician',
                      'mnk' => 'Monk',       'monk'      => 'Monk',
                      'nec' => 'Necromancer','necro'     => 'Necromancer', 'necromancer' => 'Necromancer',
                      'pal' => 'Paladin',    'pally'     => 'Paladin',     'paladin'     => 'Paladin',
                      'rng' => 'Ranger',     'ranger'    => 'Ranger',
                      'rog' => 'Rogue',      'rogue'     => 'Rogue',
                      'shm' => 'Shaman',     'shammy'    => 'Shaman',      'shaman'      => 'Shaman',
                      'shd' => 'Shadow Knight','sk'      => 'Shadow Knight','shadowknight' => 'Shadow Knight','shadow knight' => 'Shadow Knight',
                      'war' => 'Warrior',    'warrior'   => 'Warrior',
                      'wiz' => 'Wizard',     'wizzy'     => 'Wizard',       'wizard' => 'Wizard');	
	
	$output = "";
	
	// ---- transclude: all members of guild/class list ----
	if ( $options[0] == "GuildSearch" || $options[0] == "ClassSearch" || $options[0] == "ClassSearchRed" )
	{
		$searchName = str_replace("_"," ",strtolower($options[1]));
		
		// query
		$db = wfGetDB( DB_SLAVE );
		
		$queryOpt = 'page_namespace = ' . NS_MAGELO_BLUE . ' OR page_namespace = ' . NS_MAGELO_RED;
		if ( $options[0] == "ClassSearch" ) $queryOpt = 'page_namespace = ' . NS_MAGELO_BLUE;
		if ( $options[0] == "ClassSearchRed" ) $queryOpt = 'page_namespace = ' . NS_MAGELO_RED;
		
		$res = $db->select( 'page', 
		                    array( 'page_namespace','page_title' ),
												$queryOpt,
												__METHOD__,
												array( 'ORDER BY' => 'page_title ASC' )
											);
											
		if ( $db->numRows( $res ) == 0 ) {
			$wgOut->addHTML("No members found.\n");
			return;
		}
		
		if ( $options[0] == "GuildSearch" )	$output .= "Showing all members of '''<" . str_replace('\' ','\'',ucwords(str_replace('\'','\' ',$searchName))) . ">''':\n";
		if ( $options[0] == "ClassSearch" || $options[0] == "ClassSearchRed" ) $output .= "Showing all [[" . ucwords($searchName) . "]]s:\n";
		
		foreach ( $res as $row )
		{
			$search = "";
			
			// get magelo page
			$title = Title::makeTitle( $row->page_namespace, $row->page_title );
			$article = new Article($title);
			$templateText = $article->getContent();
			
			if ( $options[0] == "GuildSearch" )
			{
				// get guild name
				$ret = preg_match("/Guild:(.*?)\n/",$templateText,$match);
				if ($ret) $search = strtolower(trim($match[1]));
				
				// strip < > if present
				if ( substr($search,0,1) == "<" ) $search = substr($search,1);
				if ( substr($search,-1) == ">" ) $search = substr($search,0,-1);
				$search = str_replace("`","'",$search);
				$search = trim($search);
				
				// map guildname abbreviations
				if ( array_key_exists($search,$guildMaps) )
					$search = $guildMaps[$search];
			}
			else if ( $options[0] == "ClassSearch" || $options[0] == "ClassSearchRed" )
			{
				// get class name
				$ret = preg_match("/Class:(.*?)\n/",$templateText,$match);
				if ($ret) $search = strtolower(trim($match[1]));
				
				// map classname
				if ( array_key_exists($search,$classes) )
					$search = strtolower($classes[$search]);
			}
			
			// if guild/class name matches, add a row for this magelo page
			if ( $search == $searchName )
				$output .= "* [[" . $title->getFullText() . "|" . $title->getText() . "]]\n";
		}
		
		$output = trim($output); // strip last newline
		
		$wgOut->addHTML( $this->sandboxParse($output) );
	
	}
	// ---- transclude: list of all guilds/classes (with counts) ----
	else if ( $options[0] == "GuildList" || $options[0] == "GuildListRed" || 
	          $options[0] == "ClassList" || $options[0] == "ClassListRed" )
	{
		// start the list of "seen" guilds/classes
		$searchList = array();
		
		if ( $options[0] == "GuildList"    || $options[0] == "ClassList" ) $queryOpt = 'page_namespace = ' . NS_MAGELO_BLUE; // Blue
		if ( $options[0] == "GuildListRed" || $options[0] == "ClassListRed" ) $queryOpt = 'page_namespace = ' . NS_MAGELO_RED; // Red		
		
		// query
		$db = wfGetDB( DB_SLAVE );
		
		$res = $db->select( 'page', 
		                    array( 'page_namespace','page_title' ),
												$queryOpt,
												__METHOD__,
												array( 'ORDER BY' => 'page_title ASC' )
											);
											
		if ( $db->numRows( $res ) == 0 ) {
			$wgOut->addHTML("No members found.\n");
			return;
		}
		
		foreach ( $res as $row )
		{
			$search = "";
			
			// get magelo page
			$title = Title::makeTitle( $row->page_namespace, $row->page_title );
			$article = new Article($title);
			$templateText = $article->getContent();
			
			if ( $options[0] == "GuildList" || $options[0] == "GuildListRed" )
			{
				// get guild name
				$ret = preg_match("/Guild:(.*?)\n/",$templateText,$match);
				if ($ret) $search = strtolower(trim($match[1]));
				
				// strip < > if present
				if ( substr($search,0,1) == "<" ) $search = substr($search,1);
				if ( substr($search,-1) == ">" ) $search = substr($search,0,-1);
				$search = str_replace("`","'",$search);
				$search = trim($search);
				
				// map guildname abbreviations
				if ( array_key_exists($search,$guildMaps) )
					$search = $guildMaps[$search];
			}
			else if ( $options[0] == "ClassList" || $options[0] == "ClassListRed" )
			{
				// get class name
				$ret = preg_match("/Class:(.*?)\n/",$templateText,$match);
				if ($ret) $search = strtolower(trim($match[1]));
				
				// map classname
				if ( array_key_exists($search,$classes) )
					$search = strtolower($classes[$search]);
			}
				
			// check if we've seen it already, add to counter
			if ( !array_key_exists($search,$searchList) ) {
				if ( $search != "none" && $search != "" && $search != " " )
					$searchList[$search] = 1;
			} else {
				$searchList[$search] = $searchList[$search] + 1;
			}
			
		}
		
		// sort by number of members
		arsort($searchList);
		
		// output guild/class list
		foreach ( $searchList as $search => $count )
		{
				// capitalize each word and after a dash
				$search = str_replace('\' ','\'',ucwords(str_replace('\'','\' ',$search)));
				
				if ( $options[0] == "GuildList" || $options[0] == "GuildListRed" )
					$output .= "* [[Special:Magelo/GuildSearch/" . $search . "|" . $search . "]] ($count)\n";
				if ( $options[0] == "ClassList" )
					$output .= "* [[Special:Magelo/ClassSearch/" . $search . "|" . $search . "]] ($count)\n";
				if ( $options[0] == "ClassListRed" )
					$output .= "* [[Special:Magelo/ClassSearchRed/" . $search . "|" . $search . "]] ($count)\n";
		}
		
		$output = trim($output); // strip last newline
		
		$wgOut->addHTML( $this->sandboxParse($output) );
	
	
	}
	else
	{
		// output  
		//$wgOut->addHTML( $output );
		//$wgOut->addWikiText( $output );	
		
		$this->setHeaders();
		$wgOut->setPagetitle("This special page has no direct contents.");
	}
	
}

}
