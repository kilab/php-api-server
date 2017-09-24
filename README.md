# PHP REST API server

Create light and real REST API server with data structure generating.

**Package is still in development! First stable, fully functional version is scheduled for 1.0.0.**

**Main goals:**
 - Request and responses supports only JSON format
 - Multi web server support
 - Support for API multi versions (default is V1)
 - Custom actions in endpoints
 - Built in actions for GET requests: first, last, count
 - Support for sort, filter, limit and select fields in GET requests
 - X-HTTP-Method-Override support
 - Endpoint names are in plural with kebab-case notation to divide words
 - Valid HTTP statuses for each response type
 - Handling the relationship between the endpoints (slash as separator)
 - Multilanguage support by Accept-Languages header
 - HTTP access control (CORS) and JSONP support
 - OAuth2 support
