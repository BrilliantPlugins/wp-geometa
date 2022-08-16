WordPress has some security rules surrounding using wildcards in queries. From the [$wpdb->prepare docs](https://developer.wordpress.org/reference/classes/wpdb/prepare/):

> Literal percentage signs (%) in the query string must be written as %%. Percentage wildcards (for example, to use in LIKE syntax) must be passed via a substitution argument containing the complete LIKE string, these cannot be inserted directly in the query string.
