<?php
include '../php/config.php';
include '../php/functions.php';

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
  					"mail"=>getOr($data[$i]["mail"][0],""),
  					"employeeid"=>getOr($data[$i]["employeeid"][0],""),
  					"sam"=>$data[$i]["samaccountname"][0],
						"title"=>getOr($data[$i]["title"][0],""),
						"managedby"=>getOr($data[$i]["manager"][0],""),
						"description"=>getOr($data[$i]["description"][0],""),
						"telephone"=>getOr($data[$i]["telephonenumber"][0],""),
						"mobile"=>getOr($data[$i]["mobile"][0],""),
						"fax"=>getOr($data[$i]["facsimiletelephonenumber"][0],""),
						"ville"=>getOr($data[$i]["l"][0],""),
						"bureau"=>getOr($data[$i]["physicaldeliveryofficename"][0],""),
						"service"=>getOr($data[$i]["department"][0],""),
						"rpps"=>getOr($data[$i]["rpps"][0],""),
						"useraccountcontrol"=>getOr($data[$i]["useraccountcontrol"][0],"")
  				));
        }
			}

			ldap_control_paged_result_response($ldapconn, $result, $cookie);
		}while($cookie !== null && $cookie != '');

		//Sorting the list
		usort($adlist, 'sortBydisplayname');

		echo("
		<table id='tableComptes' class='display adTable adTableComplet'>
			<thead>
				<tr>
					<th>Utilisateur</th>
					<th>samAccountName</th>
					<th>Mail</th>
					<th>Fonction</th>
					<th>Service</th>
					<th>Bureau</th>
					<th>Ville</th>
					<th>description</th>
					<th>Gestionnaire</th>
					<th>ID</th>
					<th>Telephone</th>
					<th>Mobile</th>
					<th>Fax</th>
					<th>RPPS</th>
				</tr>
			</thead>
			<tbody>
		");

		for ($row = 0; $row < count($adlist); $row++) {
			echo("<tr>");
	    echo("<td><a href=\"detailCompte.php?id=".removeAccents($adlist[$row]['sam'])."\">".$adlist[$row]['displayname']."</a></td>");
			echo("<td>".$adlist[$row]['sam']."</td>");
			echo("<td>".$adlist[$row]['mail']."</td>");
			echo("<td>".$adlist[$row]['title']."</td>");
			echo("<td>".$adlist[$row]['service']."</td>");
			echo("<td>".$adlist[$row]['bureau']."</td>");
			echo("<td>".$adlist[$row]['ville']."</td>");
			echo("<td>".$adlist[$row]['description']."</td>");
			if ($adlist[$row]['managedby'] != ""){
				 echo("<td><a href=\"detailCompte.php?dn=".$adlist[$row]['managedby']."\">".explodeCN($adlist[$row]['managedby'])."</a></td>");
			}
			else {
				echo ("<td></td>");
			}
	    echo("<td>".$adlist[$row]['employeeid']."</td>");
			echo("<td>".$adlist[$row]['telephone']."</td>");
			echo("<td>".$adlist[$row]['mobile']."</td>");
			echo("<td>".$adlist[$row]['fax']."</td>");
			echo("<td>".$adlist[$row]['rpps']."</td>");
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
