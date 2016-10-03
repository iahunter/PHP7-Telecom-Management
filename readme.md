#Telephone Number Database / API

This is an app running on Laravel 5.2 + Dingo + JWT + Bouncer

RESTful API to create, read, update, and delete phone number blocks for number tracking. It can be called via anything capable of interacting with a RESTful/JSON API.

Authentication is possible via client TLS certificate, or LDAP if enabled and configured. User management is not in scope. 

The current intended application stack for this application is: Ubuntu 16.04 + Nginx + PHP 7 + Mysql 5.7

Bouncer roles manage rights to the numbers databases via the application http controllers.


#Backend
* Laravel framework can be found on the [Laravel website](http://laravel.com/docs).

* dingo api can be found on GitHub. https://github.com/dingo/api/wiki

* JWT can be found on GitHub. https://github.com/tymondesigns/jwt-auth/wiki

* Bouncer roles and permissions can be found on GitHub. https://github.com/JosephSilber/bouncer

#Frontend UI 
There is also a Frontend UI that is included using Bootstrap3, AngularJS 1.5, Angular-Chart.js and angular-jwt. 

* Bootstrap is at http://getbootstrap.com. 

* AngularJS is at https://docs.angularjs.org/api

* Angular Chart is at https://jtblin.github.io/angular-chart.js/

* Angular-JWT is at https://github.com/auth0/angular-jwt. 
