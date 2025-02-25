Fusio
=====

# About

Fusio is an open source API management platform which helps to build and manage 
RESTful APIs. It provides endpoint versioning, handling data from different data 
sources, schema definition (JsonSchema), automatic documentation generation and
secure authorization (OAuth2). More informations on 
http://www.fusio-project.org/

We think that there is a huge potential in the API economy. Whether you need an 
API to expose your business functionality or to develop One-Page web 
applications or Mobile-Apps. Because of this we think that Fusio is a great tool 
to simplify building such APIs.

# Installation

To install Fusio download the latest version and place the folder into the www 
directory of the webserver. After this Fusio can be installed in the following 
steps.

 * __Adjust the configuration file__  
   Open the file `configuration.php` in the Fusio directory and change the key 
   `psx_url` to the domain pointing to the public folder. Also insert the 
   database credentials to the `psx_sql_*` keys.
 * __Execute the installation command__  
   The installation script inserts the Fusio database schema into the provided 
   database. It can be executed with the following command 
   `php bin/fusio install`.
 * __Create administrator user__  
   After the installation is complete you have to create a new administrator 
   account. Therefor you can use the following command `php bin/fusio adduser`. 
   Choose as account type "Administrator" and save the generated password.
 * __Adjust url for the backend app__  
   The backend to control Fusio is based on AngularJS. The app needs to know the 
   entry point of the API. Therefor you have to edit the file 
   `public/backend.htm` and change the javascript variable `fusio_url` pointing 
   to the API endpoint. After this it is possible to login to the backend at 
   `/backend.htm`.

# Contribution

If you have found any bugs or want to make feature requests use the bug tracker
on GitHub. For code contributions feel free to send a pull request through 
GitHub, there we can discuss all details of the changes.

