<?php
namespace CSV;

class Query {

    private $file;
    private $filter = null;
    private $headers = array();
    private $outpath;
    private $select;
    private $limit = 0;

    private $count = 0;

    public function __construct($source) {
       if ( file_exists($source) ) {
            $this->file = fopen($source, "r");
        } else {
            // handle bad source
            throw new \Exception("Source currently needs to be a file");
        }

        $this->headers = fgetcsv($this->file);
    }

    /**
     *  executes the parsing of input csv + writing of output csv
     *
     *
     */

    public function execute() {
        $from = $this->file;
        $to = fopen($this->outpath, "w");
        $headers = $this->headers;
        $filter = $this->filter;
        $select = isset($this->select) ? $this->select : "*";

        $getRows = array();

        if ( !isset($to) ) { throw new \Exception("No output path provided"); }

        // handle select input
        if ( $select == "*" ) {
            $getRows = array_keys($headers);
            fputcsv($to, $headers);
        } else {
            $outCols = array();

            for ($i = 0; $i < count($headers); $i++ ) {
                if ( in_array($headers[$i], $select) ) {
                    array_push($getRows, $i);
                    array_push($outCols, $headers[$i]);
                }
            }

            fputcsv($to, $outCols);
        }

        while( $row = fgetcsv($from) ) {
            $rowOut = array();
            
            if ( is_callable($filter) ) {
                $row_arr = array_combine($headers, $row);
                if ( !call_user_func($filter, $row_arr) ) {
                    continue;
                }
            }

            foreach( $getRows as $colNum ) {
                array_push($rowOut, $row[$colNum]);
            }

            fputcsv($to, $rowOut);

            $this->count++;
            if ( $this->limit && $this->count == $this->limit ) { break; }
        }
    }

    /**
     *  alias for CSV\Query::where()
     *
     *  @param  callable   filter callable, takes single param of 
     *                     associative array row w/ headers as keys + row's values
     *  @return CSV\Query  this instance
     */

    public function filter($filter = null) {
        return $this->where($filter);
    }

    /**
     *  set a limit for number of lines returned
     *
     *  @param int
     *  @return CSV\Query  this instance
     */

    public function limit($limit = 0) {
        $this->limit = $limit;
        return $this;
    }

    /**
     *  select rows to return
     *
     *  @param  string|array  "*", comma-delimited list, or array
     *  @return CSV\Query     this instance
     */

    public function select($which = "*") {
        $this->select = $which;
        return $this;
    }

    /**
     *  adds output location (defaults to stdout)
     *
     *  @param  string    file location
     *  @return CSV\Query this instance
     */

    public function to($location = "php://output") {
        $this->outpath = $location;
        return $this;
    }

    /**
     *  applies filter to query
     *
     *  @param  callable   filter callable, takes single param of 
     *                     associative array row w/ headers as keys + row's values
     *  @return CSV\Query  this instance
     */

    public function where($filter = null) {
        if ( !is_callable($filter) ) {
            $filter = null;
        }

        $this->filter = $filter;
        return $this;
    }
}