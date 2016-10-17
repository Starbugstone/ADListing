# ADListing

An AD lister in PHP to list all users and groups with the details.

All in french language for the moment and the AD has a custom field named RPPS for doctors.

You can list all users directly from Active Directory. Search using the great [DataTables](https://github.com/DataTables/DataTables) plugin for jquery
![UserListing](https://cloud.githubusercontent.com/assets/17267969/19436015/2cd9e768-946e-11e6-9005-e150243eec3d.png)

View all the details of an account including group membership, manager relationship, phone numbers and more.
![UserDetails](https://cloud.githubusercontent.com/assets/17267969/19436016/2f1a3686-946e-11e6-8137-49fcef6e36fa.png)

Using the excelent [OrgChart](https://github.com/dabeng/OrgChart), view your organisation with a dynamic treeview
![Chart](https://cloud.githubusercontent.com/assets/17267969/19436017/307f7c02-946e-11e6-80c6-7ca2f8bd6a8c.png)

you need an apache / php (Version 5) server with the LDAP extension enabled. just use [Xampp](https://www.apachefriends.org) for a quick and dirty install or set up a full blown LAMP server for better performance.

edit the config.php file in the php folder to get things up and running.
