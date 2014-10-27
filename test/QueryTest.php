<?php
class QueryTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->filename = "test/test.csv";
        $this->expected = file_get_contents($this->filename);
        $this->csv = new CSV\Query($this->filename);
    }

    public function testSelectAsterisk() {
        $res = $this->csv->select("*");
        $this->assertEquals($res, $this->expected);
    }

    public function testSelectAsteriskAndColumns() {
        $res = $this->csv->select(array("Header A", "Header C"));
        $this->assertNotEquals($res, $this->expected);

        $split = explode("\n", $res);

        foreach($split as $line) {
            $line_expl = explode(",", $line);
            $this->assertCount(2, $line_expl);
        }
    }

    /**
     *  @expectedException Exception
     */

    public function testThrowsExceptionIfWrongHeader() {
        $res = $this->csv->select(array("nope", "not that one"));
        $this->fail("Should have thrown an Exception for header that doesn't exist");
    }

    public function testFilter() {
        $res = $this->csv->where(function($row) {
            foreach($row as $col) {
                return !preg_match("/\d+/", $col);
            }
        })->select("*");

        $this->assertNotEquals($res, $this->expected);
    }
}