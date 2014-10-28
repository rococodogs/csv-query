<?php
class QueryTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->filename = "test/test-master.csv";
        $this->expected = file_get_contents($this->filename);
        $this->csv = new CSV\Query($this->filename);
    }

    public function testOneForOne() {
        $this->csv
             ->to("test/test-duplicate.csv")
             ->select("*")
             ->execute()
             ;

        $this->assertEquals($this->expected, file_get_contents("test/test-duplicate.csv"));
    }

    public function testFilter() {
        $this->csv
             ->to("test/test-duplicate.csv")
             ->where(function($row) { return $row['COUNTRY'] == "USA"; })
             ->select("*")
             ->execute()
             ;

        $this->assertEquals($this->expected, file_get_contents("test/test-duplicate.csv"));

        $this->csv
             ->to("test/test-duplicate.csv")
             ->where(function($row) { return $row['COUNTRY'] == "Canada"; })
             ->select("*")
             ->execute()
             ;
        $this->assertNotEquals($this->expected, file_get_contents("test/test-duplicate.csv"));

        $test_count = -1;
        $dup_count = -1;

        $file = fopen($this->filename, "r");
        while(fgetcsv($file)) { $test_count++; }
        fclose($file);

        $file = fopen("test/test-duplicate.csv", "r");
        while(fgetcsv($file)) { $dup_count++; }
        fclose($file);

        $this->assertEquals(1, $test_count);
        $this->assertEquals(0, $dup_count);
    }

    public function testSelect() {
        $this->csv
             ->to("test/test-duplicate.csv")
             ->select(array("LOGINID", "DISPLAYNAME"))
             ->execute()
             ;

        $file = fopen("test/test-duplicate.csv", "r");
        while($row = fgetcsv($file)) {
            $this->assertEquals(2, count($row));
        }

        fclose($file);
    }
}