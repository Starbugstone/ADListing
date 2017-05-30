<?php
// config

//racine du site
$racineSite = 'http://Comptes';

//Serveur AD, identifiants et rootDN
$ldapserver = 'ad.foo.bar';
$ldapuser = 'lectureAD';
$ldappass = 'pass';
$ldapAdminuser = 'modifAD';
$ldapAdminpass = 'pass2';
$ldaptree = 'DC=foo,DC=bar';
$ldapdomain = 'foo'; //used in the form 'domain\user'

//the group membership is not recursive. Each user has to be a direct member.
//Code refactoring can be done if needs be.
//Memory help for ldap query
/*
public function inGroup($ldapConnection, $userDN, $groupToFind) {
    $filter = "(memberof:1.2.840.113556.1.4.1941:=".$groupToFind.")";
    $search = ldap_search($ldapConnection, $userDN, $filter, array("dn"), 1);
    $items = ldap_get_entries($ldapConnection, $search);
    if(!isset($items["count"])) {
        return false;
    }
    return (bool)$items["count"];
}
*/

//group name of admins who will have extra buttons defined. Has to be the full CN Name found in the memberof dump
$ldapExtraAdminGroup = 'CN=Groupe Admin lectureAD,OU=LDAP,DC=foo,DC=bar';

//membres de ce groupe peuvent mettre a jour les infos des utilisateurs
$ldapRHAdminGroup = 'CN=Groupe AdminAD RH,OU=LDAP,DC=foo,DC=bar';

//membres de ce groupe ne peuvent pas etre desactiver par le module RH
$nonDisactivatableAccountGroup = 'CN=Groupe AdminAD RH Non Desactivable,OU=LDAP,DC=foo,DC=bar';

//adresse mail pour l'alerte des compte desactivé
$alertMailForDisactivation = "mail@foo.bar";

$mailConfig = array(
  'host'=>"mail.foo.bar",
  'SMTPAuth'=>False,
  'username' => '',
  'password' => '',
  'SMTPSecure' => '',
  'port' => '',
  'setFrom' =>'noreply@foo.bar',
  'setTo' => 'default@foo.bar',
  'replyTo' => 'noreply@foo.bar'
);

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
  'OU=LDAP',
  'CN=Builtin',
  'CN=Microsoft Exchange System Objects',
  'OU=Microsoft Exchange Security Groups',
  'OU=BAL Groupe hors securité'
];

//Color coding Organizational chart
//We will color code members of diffrent groups. need to have the full distinguished name of the group followed by a class name
//if the class doesn't exist, need to add it in style.css
$orgChartColors = [
  ["CN=cadresAS,OU=Gestion,DC=foo,DC=bar","cadreAiderSante"],
  ["CN=MedecinAS,OU=Gestion,DC=foo,DC=bar","medecinsAiderSante"]
];

//Custom AD Field used for our doctors
$customRPPSField = true;

?>
