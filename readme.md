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

### `CSV\Query::filter(callable $callback)`
filters each row via provided callback, which is provided an associative array representing the row (keys are headers). note that keys _must_ match header case. (also available as `CSV\Query::where()`)

### `CSV\Query::limit(int $limit)`
limit the number of items returned

### `CSV\Query::select(string|array $select)`
choose which columns to return. `*` will return all columns.

### `CSV\Query::transform(callable $callback)`
applies transformation to each row via provided callback, which is provided an associative array representing the row (keys are headers). again, note that keys _must_ match header case.

**NOTE** case-sensitive

### `CSV\Query::to(string $output_location)`
sets the export path, defaults to `php://output`

### `CSV\Query::where(callable $callback)`
apply a filter function to each row. provides the callable with an associative array whose keys are the CSV's headings.

## after a query's execution, there are a few methods to call that provide some info about the query

### `CSV\Query::getCount([boolean $includeHeaders = false])`
returns item count. use `$csv->getCount(true)` to include header row

### `CSV\Query::getFilter()`
returns filter callable

### `CSV\Query::getHeaders()`
returns array of output headers

### `CSV\Query::getLimit()`
returns the item limit

### `CSV\Query::getLineCount()`
returns the input csv file's line count. **note:** if `CSV\Query::limit()` is used, this number will be the total number of rows read _until_ the limit was reached

### `CSV\Query::getOutputPath()`
returns the path to the output file

### `CSV\Query::getRawHeaders()`
returns array of input headers