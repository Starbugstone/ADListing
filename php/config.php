<?php
// config

//Serveur AD, identifiants et rootDN
$ldapserver = '';
$ldapuser = '';
$ldappass = '';
$ldaptree = '';

//Liste des OU a ne pas prendre en compte pour la recherche des utilisateurs et groupes
//un regex est fait sur ces données donc considerer qu'ils sont entourer de WildCard
$refusedOU = [
  'CN=Builtin',
  'CN=Microsoft Exchange System Objects',
  'OU=Microsoft Exchange Security Groups'
]

?>
