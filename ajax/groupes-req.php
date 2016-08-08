<?php
include '../php/config.php';
include '../php/functions.php';


$filter = "(&(objectCategory=group)(samaccountname=*))";

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


		$adlist=array(); //initialise array
		do{
			ldap_control_paged_result($ldapconn, $pageSize, true, $cookie); //initialise pagination based on cookie

			$result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
			$data = ldap_get_entries($ldapconn, $result);

			for ($i=0; $i<$data["count"]; $i++) {
				if(blacklistedDistinguishedname($data[$i]["distinguishedname"][0], $refusedOU) == FALSE){
					array_push($adlist,array(
						"sam"=>$data[$i]["samaccountname"][0],
						"cn"=>$data[$i]["cn"][0],
						"number"=>getOr($data[$i]["member"]['count'],"0"),
						"mail"=>getOr($data[$i]["mail"][0],""),
						"managedby"=>getOr($data[$i]["managedby"][0],""),
						"grouptype"=>getOr($data[$i]["grouptype"][0],"")
					));
				}
			}

			ldap_control_paged_result_response($ldapconn, $result, $cookie);

		}while($cookie !== null && $cookie != '');


		//need to find a way to sort alpha
		//this works a but but not great. Data Tables takes care of it though
		 function sortBySam($a, $b) {
		   return strcmp($a['sam'], $b['sam']);
		 }
		 usort($adlist, 'sortBySam');

		 echo("
		 <table id='tableGroupes' class='display adTable'>
			 <thead>
				 <tr>
					 <th>Groupe</th>
 					 <th>Membres</th>
					 <th>Mail</th>
					 <th>Gestionnaire</th>
				 </tr>
			 </thead>
			 <tbody>
		 ");

		 for ($row = 0; $row < count($adlist); $row++) {

			 echo("<tr>");

			 echo("<td><a href=\"detailGroupe.php?id=".$adlist[$row]['sam']."\">");
			 		echo ($adlist[$row]['cn']);
			 		if ($adlist[$row]['grouptype']<0){
						echo("<i class='fa fa-shield secIcon' aria-hidden='true' title='Groupe de securite'></i>");
					}else{
						echo("<i class='fa fa-users secIcon' aria-hidden='true' title='Groupe de distribution'></i>");
					}

			 echo ("</a></td>");
			 echo("<td>".$adlist[$row]['number']."</td>");
			 echo("<td>");
			 echo ($adlist[$row]['mail']);
			 if ($adlist[$row]['mail'] !=""){
				 echo ("<a href=\"mailto:".$adlist[$row]['mail']."\"><i class='fa fa-envelope-o secIcon' aria-hidden='true' title='Envoyer Mail'></i></a>");
			 }
			 echo ("</td>");

			 if ($adlist[$row]['managedby'] != ""){
			 		echo("<td><a href=\"detailCompte.php?dn=".$adlist[$row]['managedby']."\">".explodeCN($adlist[$row]['managedby'])."</a></td>");
			 }
			 else {
				 echo ("<td></td>");
			 }

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
