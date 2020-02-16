<?php
      
/*************************************************

p99wiki - extensions (AjaxHoverHelper)
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
			
class AjaxHoverHelper extends SpecialPage {

function __construct()
{
  parent::__construct( "AjaxHoverHelper" );
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
  global $wgRequest, $wgOut, $wgUser, $wgParser;

  // disable normal output
  $wgOut->disable();

  // set header
  header( "Content-type: text/html; charset=utf-8" );
 
  // parse request
  $reqName = trim($par);

  if (!$reqName) {
    print "<p>AjaxHoverHelper Error 1.</p>";
    return;
  }

  $parser = new Parser;
  $poptions = new ParserOptions;

  //print "<p>request: " . $reqName . "</p>";

  $tTitle = Title::newFromText( $parser->transformMsg($reqName, $poptions) );

  $templateText = $parser->fetchTemplate( $tTitle );

  if ( strpos($templateText,"{{Itembox") !== false && strpos($templateText,"{{Itempage") !== false )
  {
    // old (double) format
    $start = strpos($templateText,"{{Itembox");
    $end   = strpos($templateText,"}}");
    $templateText = substr($templateText,$start,$end-$start+2);

    $htmlText = $wgOut->parse( $templateText );
    print $htmlText;

    return;
  }
  elseif ( strpos($templateText,"{{Itempage") !== false && strpos($templateText,"{{Itembox") === false)
  {
    // new (single) format

  }
  elseif ( strpos($templateText,"{{Namedmobpage") !== false )
  {
    // mob page with mobStatsBox
    $htmlText = $wgOut->parse( $templateText );

    $start = strpos($htmlText,'<table cellspacing="3" class="mobStatsBox">');
    $end   = strpos($htmlText,'</table>');
    $htmlText = substr($htmlText,$start,$end-$start+8);

    print $htmlText;

    // mob image
    //$parms = parseTemplateParameters($templateText);
    //print $parms[0];
    //print "hihi";
    return;
  }
  else
  {
    // unrecognized page
    print "<p>AjaxHoverHelper Error 2.</p>";
    //print "<p>template: " . $templateText . "</p>";

    return;
  }
}

}
