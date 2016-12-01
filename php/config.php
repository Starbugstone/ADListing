<?php
// config

//Serveur AD, identifiants et rootDN
$ldapserver = ''; //the LDAP server to conenct to
$ldapuser = ''; //read only user
$ldappass = ''; 
$ldapAdminuser = ''; //used so the user can update elements. See vars.php
$ldapAdminpass = ''; //ldap admin password
$ldaptree = ''; //AD Root
$ldapdomain = ''; //used in the form 'domain\user'

//group name of admins who will have extra buttons defined. Has to be the full CN Name found in the memberof dump
$ldapExtraAdminGroup = 'true';

//membres de ce groupe peuvent mettre a jour les infos des utilisateurs
$ldapRHAdminGroup = 'CN=Groupe AdminAD RH,OU=LDAP,DC=aider,DC=asso,DC=fr';

//chemin fichier LOG
$logFolder = 'C:\xampp\htdocs\Comptes\log\\';

//should we bypass the user rights and use admin rights.
//need to do this if you want to allow users to update more sensitive information
//By default, users can update Office and phone numbers only
//the admin user can be member of the Account Operator group which can update all users except admin users.
//or just have deliagtion on the AD. Try to be as restrictive as possible with the account.
$bypassUserRights = true;

//Liste des OU a ne pas prendre en compte pour la recherche des utilisateurs et groupes
//un regex est fait sur ces données donc considerer qu'ils sont entourer de WildCard
$refusedOU = [
  'CN=Builtin',
  'CN=Microsoft Exchange System Objects',
  'OU=Microsoft Exchange Security Groups'
]

//Color coding Organizational chart
//We will color code members of diffrent groups. need to have the full distinguished name of the group followed by a class name
//if the class doesn't exist, need to add it in style.css
$orgChartColors = [
  ["CN=Groupe,OU=GroupeOU,DC=domaine,DC=com","css class"]
];

//Custom AD Field used for our doctors
$customRPPSField = true;


?>
