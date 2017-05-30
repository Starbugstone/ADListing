<?php
include '../php/config.php';
include '../php/functions.php';

$manager=$_GET['manager'];
//filtre
$filter='(&(objectCategory=person)(samaccountname=*)(!(useraccountcontrol:1.2.840.113556.1.4.803:=2)))';
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

  		$pageSize = 100; //pagesize and cookie for pagination to enable return of more than 100 results
          $cookie = '';

  		$adlist=array();
  		do {
  			ldap_control_paged_result($ldapconn, $pageSize, true, $cookie);
  			$result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
  			$data = ldap_get_entries($ldapconn, $result);

  			for ($i=0; $i<$data["count"]; $i++) {
          if (blacklistedDistinguishedname($data[$i]["distinguishedname"][0],$refusedOU) == FALSE){
    				array_push($adlist,array(
              "displayname"=>getOr($data[$i]["displayname"][0],$data[$i]["cn"][0]),
              "distinguishedname"=>$data[$i]["distinguishedname"][0],
    					"sam"=>$data[$i]["samaccountname"][0]
    				));
          }
  			}

  			ldap_control_paged_result_response($ldapconn, $result, $cookie);
  		}while($cookie !== null && $cookie != '');

  		//Sorting the list
  		usort($adlist, 'sortBydisplayname');

  		echo("<select class=\"form-control\" id=\"manager\" name=\"manager\">");
      if($manager==""){
        echo("<option></option>");
      }
  		for ($row = 0; $row < count($adlist); $row++) {
        //checking if manager was already enables
        if($manager == $adlist[$row]['distinguishedname']){
          $selected = "selected=\"selected\"";
        }else{
          $selected = "";
        }
        echo("<option ".$selected."value=\"".$adlist[$row]['distinguishedname']."\">".$adlist[$row]['displayname']."</option>");

  		}
  		echo("</select>");
    } else {
        echo "LDAP bind failed...";
    }

}

// all done? clean up
ldap_close($ldapconn);

 ?>
