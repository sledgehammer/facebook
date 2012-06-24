# Facebook

Object oriented API for using Facebook's Social Graph.

* Automaticly fetches all allowed fields
* Caches "/me" and "/me/friends" when using the FacebookUser object in the session for improved performance.
* Gives hint which permissions are missing when you access a protected property.

## Idea's / Todo

* prefetch next page in a paginated resultset.
* notice() when the (automatic) pageLimit is reached.
