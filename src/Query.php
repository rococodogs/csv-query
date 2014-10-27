<?php
namespace CSV;

class Query {

    private $is_file;
    private $filter = null;
    private $headers = array();
    private $content = array();
    private $line = 1;

    public function __construct($source) {
        if ( preg_match("/\,|\t\|/", $source) ) {
            $this->is_file = false;
        } elseif ( file_exists($source) ) {
            $this->is_file = true;
            $this->file = fopen($source, "r");
        } else {
            // handle bad source
            throw new \Exception("Source needs to be a string or a file");
        }

        $this->headers = fgetcsv($this->file);
        $this->line++;
    }

    public function select($which = "*") {
        // if our 'which' was supplied as "field1,field2", make it array
        if ( is_string($which) && preg_match("/\,/", $which) ) {
            $which = preg_split("/\,/", $which);
        } 
        
        // or if we've only been provided a single field, put _that_ into an array
        elseif ( $which != "*" ) {
            $which = (array) $which;
        }

        if ( is_array($which) ) {
            foreach($which as $u_col) {
                if ( !in_array($u_col, $this->headers) ) { 
                    throw new \Exception("Column '" . $u_col . "' not in file");
                }
            }
        }

        $res = array();

        while ( $row = fgetcsv($this->file) ) {
            $this->line++;

            // results array for the row
            $row_results = array();

            // row as an associative array
            $row_arr = $this->rowToAssociativeArray($row);

            if ( is_callable($this->filter) ) {
                if ( !call_user_func($this->filter, $row_arr) ) { 
                    continue; 
                }
            }

            if ( is_array($which) ) {
                foreach($which as $col) {
                    array_push($row_results, $row_arr[$col]);
                }
            } else {
                $row_results = $row;
            }

            array_push($res, implode(",", $row_results));
            
        }

        array_unshift($res, implode(",", ($which == "*" ? $this->headers : $which)));
        return implode("\n", $res);
    }

    public function where($filter = null) {
        $this->filter = $filter;
        return $this;
    }

    private function rowToAssociativeArray($row) {
        $number_of_cols = count($this->headers);
        $out = array();

        for( $i = 0; $i < $number_of_cols; $i++ ) {
            $out[$this->headers[$i]] = $row[$i];
        }

        return $out;
    }
}