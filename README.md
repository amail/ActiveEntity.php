ActiveEntity.php
================

Simple library for creating entities/collections in PHP that are mapped to keys and lists in Redis.

## Features

### Collection

 * Change sort order and sort by specific property.
 * Implements Iterable, so you can iterate directly on it.
 * Search for entities with a specific property value.
 * Find a single entity by ID.

### Entity

 * Change Tracking (if you haven't changed a value, it won't be commited if not explicitly implied).
 * Property loading only done when neccessary, or explicitly implied.

### Logging

 * DataStore implementation that intercepts all commands to Redis and writes them to a logger.

## Convention based

ActiveEntity relies heavily on conventions. I.e. if you create a collection called 'ProductItemCollection', then it is implied that it consists of entities with the type name 'ProductItem'.
The same way, when you create a entity called 'ProductItem' it is automatically implied, that if there is a collection for it, the type name is 'ProductItemCollection'.

* Entities should be named '{EntityName}', i.e. 'ProductItem'.
* Collections should be named '{EntityName}Collection', i.e. 'ProductItemCollection'.
* Property names should be all lower-case, with undercase as seperation between words. I.e. 'control_number'. Using this convention, the property can be accessed using $entity->getControlNumber() and set by using $entity->setControlNumber($value), a.s.o.
