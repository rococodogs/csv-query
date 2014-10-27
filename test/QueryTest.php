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
    }

    public function testSelectAsteriskAndColumnsCaseInsensitive() {
        $res = $this->csv->select(array("header a", "header c"));
        $this->assertNotEquals($res, $this->expected);
    }

    /**
     *  @expectedException Exception
     */

    public function testThrowsExceptionIfWrongHeader() {
        $res = $this->csv->select(array("nope", "not that one"));
        $this->fail("Should have thrown an Exception for header that doesn't exist");
    }
}