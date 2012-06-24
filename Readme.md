# Facebook

Object oriented API for using Facebook's Social Graph.

## Features
* Autocompletion for common facebook objects.
* Automaticly fetches all allowed fields
* Gives hint which permissions are missing when you access a protected property.
* Fetches connected objects as properties. $me->friends
* Connected objects are Collection object, for easy filtering & sorting. $me->friends->orderBy('last_name');
* Caches "/me" and "/me/friends" when using the FacebookUser object for improved performance.

## Improvements in the Facebook class.

 * Easy connect.
 * shorthand for get/post/delete requests.
 * shorthand for executing a fql query.
 * Validates requested permissions.
 * Logs api calls and measures executionTime.
 * api() accepts $parameters['fields'] as an array.
 * all() fetches multiple pages in a paginated result and returns the merged array.
 * Singleton pattern, access Facebook from anywhere in your application.

## Idea's / Todo

* prefetch next page in a paginated resultset.
* notice() when the (automatic) pageLimit is reached.
* Implement all documented facebook entities & connections.
* Implement writing api as methods in the GraphObject.
* Implement ActiveRecord methods. save(), delete()
* Lazily fetch the result object when creating a post.