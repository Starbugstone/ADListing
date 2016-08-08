<?php
include '../php/config.php';
include '../php/functions.php';

//filtre
$filter='(&(objectCategory=person)(samaccountname=*)(!(useraccountcontrol=514)))';
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
            "cn"=>$data[$i]["cn"][0],
  					"mail"=>getOr($data[$i]["mail"][0],""),
  					"employeeid"=>getOr($data[$i]["employeeid"][0],""),
  					"sam"=>$data[$i]["samaccountname"][0],
						"title"=>getOr($data[$i]["title"][0],""),
						"managedby"=>getOr($data[$i]["manager"][0],"")
  				));
        }
			}

			ldap_control_paged_result_response($ldapconn, $result, $cookie);
		}while($cookie !== null && $cookie != '');

		//trying to sort the list, works a bit !!!
		function sortBySam($a, $b) {
		   return strcmp($a['sam'], $b['sam']);
		 }
		 usort($adlist, 'sortBySam');

		echo("
		<table id='tableComptes' class='display adTable'>
			<thead>
				<tr>
					<th>Utilisateur</th>
					<th>Mail</th>
					<th>Fonction</th>
					<th>Gestionnaire</th>
					<th>ID</th>
				</tr>
			</thead>
			<tbody>
		");

		for ($row = 0; $row < count($adlist); $row++) {
			echo("<tr>");
	    echo("<td><a href='detailCompte.php?id=".$adlist[$row]['sam']."'>".$adlist[$row]['cn']."</a></td>");
			echo("<td>");
			echo ($adlist[$row]['mail']);
			if ($adlist[$row]['mail'] !=""){
				echo ("<a href='mailto:".$adlist[$row]['mail']."'><i class='fa fa-envelope-o secIcon' aria-hidden='true' title='Envoyer Mail'></i></a>");
			}
			echo ("</td>");
			echo("<td>".$adlist[$row]['title']."</td>");
			if ($adlist[$row]['managedby'] != ""){
				 echo("<td><a href='detailCompte.php?dn=".$adlist[$row]['managedby']."'>".explodeCN($adlist[$row]['managedby'])."</a></td>");
			}
			else {
				echo ("<td></td>");
			}				
	    echo("<td>".$adlist[$row]['employeeid']."</td>");
			echo("</tr>");
		}
		echo("</tbody></table>");
    } else {
        echo "LDAP bind failed...";
    }

}

// all done? clean up
ldap_close($ldapconn);

 ?>
