<?php
namespace CSV;

class Query {

    private $is_file;
    private $filter = null;
    private $headers = array();
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
        $out_headers = array();

        if ( is_string($which) && preg_match("/\,/", $which) ) {
            $which = preg_split("/\,\s?/", $which);
        } elseif ( $which != "*" ) {
            $which = (array) $which;
        }

        if ( is_array($which) ) {
            foreach($which as $col) {
                foreach($this->headers as $h) {
                    $in_arr = false;
                    if ( strtolower($col) == strtolower($h) ) {
                        $in_arr = true;
                        break;
                    }
                }
            }

            if ( !$in_arr ) { throw new \Exception("Column '" . $col . "' not in file"); }
        }

        $res = array();

        while ( $row = fgetcsv($this->file) ) {
            $this->line++;
            $row_res = array();

            if ( is_callable($this->filter) ) {
                if ( !$this->filter($row) ) { 
                    continue; 
                }
            }

            if ( $which == "*" ) {
                array_push($res, implode(",", $row));
            } else {
                foreach($which as $c) {
                    $num = array_search($c, $this->headers);
                    array_push($row_res, $row[$num]);
                }

                array_push($res, implode(",", $row_res));
            }
        }

        array_unshift($res, implode(",", ($which == "*" ? $this->headers : $which)));
        return implode("\n", $res);
    }

    public function where($filter = null) {
        $this->filter = $filter;
    }
}