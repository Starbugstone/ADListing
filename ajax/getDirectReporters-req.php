<?php
include '../php/config.php';
include '../php/functions.php';

$user=$_GET['user'];
//filtre
$filter='(&(objectCategory=person)(samaccountname='.$user.'))';
//useraccountcontrol 514 is disactivated

// connect
$ldapconn = ldap_connect($ldapserver) or die("Could not connect to LDAP server.");

if($ldapconn) {
	// Adding options
	ldap_set_option ($ldapconn, LDAP_OPT_REFERRALS, 0);
	ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    // binding to ldap server
    $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass) or die ("Error trying to bind: ".ldap_error($ldapconn));
    // verify binding
    if ($ldapbind) {


		$result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
		$data = ldap_get_entries($ldapconn, $result);

		if (!isset($data[0])){
      //bug in IE with utf-8 encoding
      $filter = utf8_encode($filter);
      $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
      $data = ldap_get_entries($ldapconn, $result);
      //echo $filter;
    }

		$directReportsError = "Aucun Colaborateur";
    if (isset($data[0]['directreports'])){
      $directReports = $data[0]['directreports'];
      array_shift($directReports);
      $directReports = nonBlacklistedDistunguishednameArray($directReports,$refusedOU);
      natcasesort($directReports);
    }else{
      $directReports = $directReportsError;
    }

		if($directReports!=$directReportsError AND count($directReports)>0 ){
			echo("<p><b>Gestionnaire de&nbsp;:</b></p><ul class='colaboList'>");
			foreach( $directReports as $colabo) {
				//Get rid of all the excess CN and OU
				if (blacklistedDistinguishedname($colabo,$refusedOU) == FALSE){

					//get display name from AD
					$filter1 = "(&(objectCategory=person)(distinguishedname=$colabo))";
					$result1 = ldap_search($ldapconn,$ldaptree, $filter1) or die ("Error in search query: ".ldap_error($ldapconn));
					$data1 = ldap_get_entries($ldapconn, $result1);
					$colaboName = getOr($data1[0]["displayname"][0],$data1[0]["cn"][0]);
					echo ("<li><a href=\"detailCompte.php?dn=".removeAccents($colabo)."\">".$colaboName."</a></li>");
				}
			}
			echo("</ul>");
		}


  } else {
      echo "LDAP bind failed...";
  }

}

// all done? clean up
ldap_close($ldapconn);

 ?>
