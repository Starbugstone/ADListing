<?php
function getOr(&$var, $default) {
    if (isset($var)) {
        return $var;
    } else {
        return $default;
    }
}

function explodeCN($cn){
  $res1 = explode(",",$cn,2);
  $res2 = explode("=",$res1[0]);
  if (isset($res2[1])){
    return $res2[1];
  }
  else{
    return $res2[0];
  }
}

function sortByCn($a, $b) {
   return strcmp($a['cn'], $b['cn']);
 }

function sortBySam($a, $b) {
  return strcmp($a['sam'], $b['sam']);
}

function debugToConsole( $data ) {

    if ( is_array( $data ) )
        $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
    else
        $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";

    echo $output;
}


//check if the element's CN is blacklisted againsed a $refused list.
//$refused is configured in config file
function blacklistedDistinguishedname($distName, $refused=""){
  $blacklisted = FALSE;
  foreach ($refused as $refusedOU) {
    $pattern = '/'.preg_quote($refusedOU, '/') . '/';
    if (preg_match($pattern, $distName)){
      $blacklisted = TRUE;
    }
  }
  return $blacklisted;
}


function escapeLdapFilter($str = '') {

// The characters that need to be escape.
//
// NOTE: It’s important that the slash is the first character replaced.
// Otherwise the slash added by other replacements will then be
// replaced as well, resulted in double-escaping all characters
// replaced before the slashes were replaced.
//
$metaChars = array(
chr(0x5c), // \
chr(0x2a), // *
chr(0x28), // (
chr(0x29), // )
chr(0x00) // NUL
);

// Build the list of the escaped versions of those characters.
$quotedMetaChars = array ();
foreach ($metaChars as $key => $value) {
$quotedMetaChars[$key] = '\\' .
str_pad(dechex(ord($value)), 2, '0', STR_PAD_LEFT);
}

// Make all the necessary replacements in the input string and return
// the result.
return str_replace($metaChars, $quotedMetaChars, $str);
}



?>
