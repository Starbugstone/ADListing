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
?>
