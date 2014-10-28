# csv-query
this is a work in process!

## usage

say you've got a csv file set up like so:

```csv
TITLE,AUTHOR,DATE,PUBLISHER,FORMAT,PAGES,"DUE DATE"
"Title of book","Famous Author","12-01-2004","Big Publisher","Paperback",336,-
```
and so on for thousands of rows.

set up a script like so:

```php
$csv = new CSV\Query("path/to/file.csv");
$csv->to("path/to/export")
    ->select("*")
    ->where(function($row){
        return $row['DATE'] < date("m-d-Y", strtotime("last year"));
    })
    ->limit(1000)
    ->execute()
    ;
```

and before you can say "snack time anytime" you'll have a new csv file waiting for you!

## api

### `CSV\Query::execute()`
executes the query

### `CSV\Query::limit(int $limit)`
limit the number of items returned

### `CSV\Query::select(string|array $select)`
choose which columns to return. `*` will return all columns.

**NOTE** case-sensitive

### `CSV\Query::to(string $output_location)`
sets the export path, defaults to `php://output`

### `CSV\Query::where(callable $callback)`
apply a filter function to each row. provides the callable with an associative array whose keys are the CSV's headings.