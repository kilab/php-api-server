# Changelog

2017-10-23 (0.5.0):
  - Added PHPUnit as composer dev dependency
  - **Removed API versioning**

2017-10-23 (0.4.0):
  - Added file generator
  - Added temporary info endpoint as default endpoint
  - Added redirect to default endpoint if is not specified
  - Added support to custom actions in endpoints
  - Added basic support to entity relations
  - Improved NGINX support and errors in production env
  - Refactored: Request class replaced by Symfony Request
  - Refactored: /bin/db.php to /bin/console.php

2017-10-16 (0.3.0):

 - Added console commands (CLI) support
 - Added database schema generator
 - Added Illuminate Database (Eloquent) integration
 - Removed Doctrine ORM
 - Fixes in error handler

2017-09-27 (0.2.0):

 - Doctrine ORM included
 - Added CRUD actions in main controller
 - Refactored: Model classes to Entity classes
 - Custom JsonResponse class replaced Symfony's JsonResponse class

2017-09-24 (0.1.0):

 - API package moved to API server repository
 - Versioning started
