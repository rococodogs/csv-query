<?php
namespace CSV;

class Query {

    private $is_file;
    private $file;
    private $filter = null;
    private $headers = array();
    private $outpath;

    public function __construct($source) {
       if ( file_exists($source) ) {
            $this->is_file = true;
            $this->file = fopen($source, "r");
        } else {
            // handle bad source
            throw new \Exception("Source needs to be a string or a file");
        }

        $this->headers = fgetcsv($this->file);
    }

    public function select($which = "*") {
        $from = $this->file;
        $to = fopen($this->outpath, "w");
        $headers = $this->headers;
        $filter = $this->filter;

        $getRows = array();

        if ( !isset($to) ) { throw new \Exception("No output path provided"); }

        // handle "which" input
        if ( $which == "*" ) {
            $getRows = array_keys($headers);
            fputcsv($to, $headers);
        } else {
            $outCols = array();

            for ($i = 0; $i < count($headers); $i++ ) {
                if ( in_array($headers[$i], $which) ) {
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
        }
    }

    public function to($location) {
        $this->outpath = $location;
        return $this;
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