WP_GeoQuery
===========

This is pre-alpha and shouldn't be used for anything yet. 

The goal of this plugin is to create very WordPressy spatial support, allowing 
WordPress users to do spatial stuff in a way which fits in with the rest of
the WordPress environment.

If done well, this would become a standard that other spatial plugin developers could
work against.

We are creating and releasing this plugin in the hopes of increasing the use of
spatial in the WordPress world. 

The intention is to provide a WordPress plugin which will allow WordPress objects 
(Posts, Comments and Users) to have spatial meta attached, and to provide 
support for spatial query operations within WP_Query. 


Game Plan / Current Status
--------------------------

WP_GeoQuery creates parallel geo meta tables for all supported object types with a 
spatial column type for the meta_value column. These tables are named 
wp_postmeta_geo, etc.

WP_GeoQuery handles (added|updated|deleted)_(post|comment|user)_meta actions and
detects when GeoJSON is being set or updated in postmeta and stores a parallel
geometrey value in the geo meta table. 

Data will be stored in EPSG:4326 since WordPress is web software and the data will 
be used to produce web maps. 4326 is also the most common format for GeoJSON, and 
for now we will assume that any GeoJSON we detect is in EPSG:4326.

We won't add a filter for get_(post|comment|user)_meta, so that the user will get
whatever they put into the meta table in the first place. 

The next step is to add filters which will allow WP_Query to handle spatial queries. 


Server Requirements
-------------------

Using WP_GeoQuery requires MySQL 5.4 or higher or MariaDB 5.3.3 or higher.

Tables are created as MyISAM tables because InnoDB tables don't (didn't?) support
spatial indexes. 


Very Likely Problems
--------------------

Older versions of MySQL used the bounding box for ST_Intersect and other queries [instead 
of the actual geometry](https://www.percona.com/blog/2013/10/21/using-the-new-mysql-spatial-functions-5-6-for-geo-enabled-applications/).

Ideally we'll work around that at some point, but let's get things working for people with 
up to date installs of MySQL first, and then work backwards.


Ideal WP_Query Support
------------------------

Since everything is stored in a meta table, ideally developers would be able to perform
meta queries, but with spatial operands instead of just =, LIKE, etc. 

We should also support the _orderby_ and _fields_ WP_Query arguments, both of which should
support ST_BUFFER, ST_DISTANCE and any other arbitrary spatial function which MySQL supports.

Some/all of this support might be implemented in a pre_get_posts filter? I'm not certain yet.
