# csv querying: how would this work?

from a large-picture standpoint, let's work through the user-interface first:

* select csv document to query
* build query using headers
    * select from columns
    * return select columns (can be different)
* return results

we'll focus on #2

## psuedo code

```
$csv = new CSV\CSV("file.csv");
$res = $csv
        ->where(function($row) { return $row['last-check-out'] < strtotime('today'); })
        ->select("*")
        ;
```