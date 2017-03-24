#Telecom Management / Number Database / RESTful API

* Used to manage phone number inventory and tracking of reserved, available, and inuse status. 

* Additional System Management Tools are being added for Cisco Call Manager but are custom settings for the author's specific environment. 
	* Custom changes are needed if being used outside of author's environment.
	* Use at your own risk. 

#App Summary

This is an app running on Laravel 5.3 + Dingo + JWT + Bouncer + L5-Swagger

RESTful API to create, read, update, and delete phone number blocks for number tracking. It can be called via anything capable of interacting with a RESTful/JSON API.

Authentication is possible via client TLS certificate, or LDAP if enabled and configured. User management is not in scope. 

The current intended application stack for this application is: Ubuntu 16.04 + Nginx + PHP 7 + Mysql 5.7

Bouncer roles manage rights to the numbers databases via the application http controllers.


#Backend
* Laravel framework can be found on the [Laravel website](http://laravel.com/docs).

* dingo api can be found on GitHub. https://github.com/dingo/api/wiki

* JWT can be found on GitHub. https://github.com/tymondesigns/jwt-auth/wiki

* Bouncer roles and permissions can be found on GitHub. https://github.com/JosephSilber/bouncer

* Auditing can be found on Github. https://github.com/owen-it/laravel-auditing

* Swagger API Documentation can be found on Github. https://github.com/DarkaOnLine/L5-Swagger

* Laravel Backups can be found on Github. https://github.com/spatie/laravel-backup

* Laravel Activity Log can be found on Github. https://github.com/spatie/laravel-activitylog

* Cisco Call Manager AXL Library can be found on Github. https://github.com/iahunter/PHP5-CallManager-AXL

#Frontend UI 
There is also a Frontend UI that is included using Bootstrap3, AngularJS 1.5, Angular-Chart.js and angular-jwt. 

* Bootstrap is at http://getbootstrap.com. 

* AngularJS is at https://docs.angularjs.org/api

* Angular Chart is at https://jtblin.github.io/angular-chart.js/

* Angular-JWT is at https://github.com/auth0/angular-jwt. 
