<?php

/*----------------------------------
info to store once loggedIn
STocked in an array which we will use for all info shown. That way we can modify the info with just one modif

do not use state, error or responseName as name of variable, reserved for the ajax reply.
anso reserved are : sAMAccountName, domainsAMAccountName and password.
'Name of variable' => array(
  'isVisablePannel' => '0-1', Visible on the logon pannel
  'isVisableModify' => '0-1', visible on the modify table
  'ldapName' => '***', ldap attribute
  'ldapNameExplodeCN' => '0-1', do we need to explode the CN
  'description' => '***', Tag used in front of the value
  'ldapErrorVal' => '***', Value to return if the ldap value does not exsist
  'isLink' => '0-1', transform the data into a link
  'linkPage' => '***', the page to call when link is clicked
  'linkPageLdapVar' => '***', LDAP variable tu use in link
  'isModifiable' => '0-1', Can the user update this on his own
  'isModifiableText' => '***' Text to show on modify page.
)

do not allow users to modify

------------------------------------*/
$loggedinInfo = array(
  'fullName' => array(
    'isVisablePannel' => '0',
    'isVisableModify' => '1',
    'ldapName' => 'displayname',
    'ldapNameExplodeCN' => '0',
    'description' => 'Nom affiché',
    'ldapErrorVal' => '',
    'isLink' => '0',
    'linkPage' => '',
    'linkPageLdapVar' => '',
    'isModifiable' => '0',
    'isModifiableText' => 'Identifiant Unique'
  ),
  'fullNameLink' => array(
    'isVisablePannel' => '1',
    'isVisableModify' => '0',
    'ldapName' => 'displayname',
    'ldapNameExplodeCN' => '0',
    'description' => 'Nom affiché',
    'ldapErrorVal' => '',
    'isLink' => '1',
    'linkPage' => 'detailCompte.php?id=',
    'linkPageLdapVar' => 'samaccountname',
    'isModifiable' => '0',
    'isModifiableText' => ''
  ),
  'fonction' => array(
    'isVisablePannel' => '1',
    'isVisableModify' => '1',
    'ldapName' => 'title',
    'ldapNameExplodeCN' => '0',
    'description' => 'Votre fonction',
    'ldapErrorVal' => 'Aucun fonction defini',
    'isLink' => '0',
    'linkPage' => '',
    'linkPageLdapVar' => '',
    'isModifiable' => '0',
    'isModifiableText' => 'Votre fonction'
  ),
  'mail' => array(
    'isVisablePannel' => '1',
    'isVisableModify' => '1',
    'ldapName' => 'mail',
    'ldapNameExplodeCN' => '0',
    'description' => 'Adresse Mail',
    'ldapErrorVal' => 'Aucun mail',
    'isLink' => '0',
    'linkPage' => '',
    'linkPageLdapVar' => '',
    'isModifiable' => '0',
    'isModifiableText' => 'Adresse mail generer par le serveur'
  ),
  'phone' => array(
    'isVisablePannel' => '1',
    'isVisableModify' => '1',
    'ldapName' => 'telephonenumber',
    'ldapNameExplodeCN' => '0',
    'description' => 'Numero de telephone',
    'ldapErrorVal' => 'Aucun telephone',
    'isLink' => '0',
    'linkPage' => '',
    'linkPageLdapVar' => '',
    'isModifiable' => '1',
    'isModifiableText' => 'Votre telephone fixe'
  ),
  'mobile' => array(
    'isVisablePannel' => '1',
    'isVisableModify' => '1',
    'ldapName' => 'mobile',
    'ldapNameExplodeCN' => '0',
    'description' => 'Numero de mobile',
    'ldapErrorVal' => 'Aucun mobile',
    'isLink' => '0',
    'linkPage' => '',
    'linkPageLdapVar' => '',
    'isModifiable' => '1',
    'isModifiableText' => 'Votre numero de mobile'
  ),
  'fax' => array(
    'isVisablePannel' => '1',
    'isVisableModify' => '1',
    'ldapName' => 'facsimiletelephonenumber',
    'ldapNameExplodeCN' => '0',
    'description' => 'Numero de Fax',
    'ldapErrorVal' => 'Aucun Fax',
    'isLink' => '0',
    'linkPage' => '',
    'linkPageLdapVar' => '',
    'isModifiable' => '1',
    'isModifiableText' => 'Votre numero de Fax'
  ),
  'managerLink' => array(
    'isVisablePannel' => '1',
    'isVisableModify' => '0',
    'ldapName' => 'manager',
    'ldapNameExplodeCN' => '1',
    'description' => 'Gestionnaire',
    'ldapErrorVal' => 'Aucun Gestionnaire',
    'isLink' => '1',
    'linkPage' => 'detailCompte.php?dn=',
    'linkPageLdapVar' => 'manager',
    'isModifiable' => '0',
    'isModifiableText' => 'Votre gestionnaire'
  ),
  'manager' => array(
    'isVisablePannel' => '0',
    'isVisableModify' => '1',
    'ldapName' => 'manager',
    'ldapNameExplodeCN' => '1',
    'description' => 'Gestionnaire',
    'ldapErrorVal' => 'Aucun Gestionnaire',
    'isLink' => '0',
    'linkPage' => '',
    'linkPageLdapVar' => '',
    'isModifiable' => '0',
    'isModifiableText' => 'Votre gestionnaire'
  ),
  'bureau' => array(
    'isVisablePannel' => '0',
    'isVisableModify' => '1',
    'ldapName' => 'physicaldeliveryofficename',
    'ldapNameExplodeCN' => '0',
    'description' => 'Bureau',
    'ldapErrorVal' => 'Aucun bureau defini',
    'isLink' => '0',
    'linkPage' => '',
    'linkPageLdapVar' => '',
    'isModifiable' => '0',
    'isModifiableText' => 'Votre Bureau'
  ),
  'service' => array(
    'isVisablePannel' => '0',
    'isVisableModify' => '1',
    'ldapName' => 'departtment',
    'ldapNameExplodeCN' => '0',
    'description' => 'Service',
    'ldapErrorVal' => 'Aucun service defini',
    'isLink' => '0',
    'linkPage' => '',
    'linkPageLdapVar' => '',
    'isModifiable' => '0',
    'isModifiableText' => 'Votre Service'
  ),
  'societe' => array(
    'isVisablePannel' => '0',
    'isVisableModify' => '1',
    'ldapName' => 'company',
    'ldapNameExplodeCN' => '0',
    'description' => 'Societe',
    'ldapErrorVal' => 'Aucun bureau defini',
    'isLink' => '0',
    'linkPage' => '',
    'linkPageLdapVar' => '',
    'isModifiable' => '0',
    'isModifiableText' => 'Votre Societe'
  ),
  'Commentaire' => array(
    'isVisablePannel' => '0',
    'isVisableModify' => '1',
    'ldapName' => 'description',
    'ldapNameExplodeCN' => '0',
    'description' => 'Commentaire',
    'ldapErrorVal' => 'Aucun Commentaire',
    'isLink' => '0',
    'linkPage' => '',
    'linkPageLdapVar' => '',
    'isModifiable' => '1',
    'isModifiableText' => 'Description de votre compte'
  )
);


?>
