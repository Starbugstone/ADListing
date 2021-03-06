<?php
include '../php/config.php';
include '../php/functions.php';

$user=$_GET['user'];
//filtre
$filter='(&(objectCategory=group)(managedBy='.$user.'))';
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
				if(blacklistedDistinguishedname($data[0]['distinguishedname'][0], $refusedOU) == FALSE){
  				array_push($adlist,array(
  					"sam"=>$data[$i]["samaccountname"][0],
						"cn"=>$data[0]['cn'][0]
  				));
				}
			}

			ldap_control_paged_result_response($ldapconn, $result, $cookie);
		}while($cookie !== null && $cookie != '');


    //sort the list by Sam
		 usort($adlist, 'sortBySam');
     if($adlist){
       echo "<p><b>Gestionnaire des groupes&nbsp;:</b></p>";
       echo("<ul>");
  		for ($row = 0; $row < count($adlist); $row++) {
  	    echo("<li><a href=\"detailGroupe.php?id=".removeAccents($adlist[$row]['sam'])."\">".$adlist[$row]['sam']."</a></li>");
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
