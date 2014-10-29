<?php
class QueryTest extends PHPUnit_Framework_TestCase {
    
    // excludes header row
    const TEST_CSV_ROW_COUNT = 2;

    const INPUT_FILENAME = "test/test-master.csv";
    const OUTPUT_FILENAME = "test/test-duplicate.csv";

    public function setUp() {
        $this->expected = file_get_contents(self::INPUT_FILENAME);
        $this->csv = new CSV\Query(self::INPUT_FILENAME);
        $this->csv->to(self::OUTPUT_FILENAME);
    }

    public function tearDown() {
        if ( file_exists(self::OUTPUT_FILENAME) ) {
            unlink(self::OUTPUT_FILENAME);
        }
    }

    public function testOneForOne() {
        $this->csv

             ->select("*")
             ->execute()
             ;

        $this->assertEquals($this->expected, file_get_contents(self::OUTPUT_FILENAME));
    }

    public function testFilter() {
        $this->csv
             ->where(function($row) { return $row['COUNTRY'] == "USA"; })
             ->select("*")
             ->execute()
             ;

        $this->assertEquals($this->expected, file_get_contents(self::OUTPUT_FILENAME));

        $this->csv
             ->where(function($row) { return $row['COUNTRY'] == "Canada"; })
             ->select("*")
             ->execute()
             ;
        $this->assertNotEquals($this->expected, file_get_contents(self::OUTPUT_FILENAME));

        $test_count = -1;
        $dup_count = -1;

        $file = fopen(self::INPUT_FILENAME, "r");
        while(fgetcsv($file)) { $test_count++; }
        fclose($file);

        $file = fopen(self::OUTPUT_FILENAME, "r");
        while(fgetcsv($file)) { $dup_count++; }
        fclose($file);

        $this->assertEquals(self::TEST_CSV_ROW_COUNT, $test_count);
        $this->assertEquals(0, $dup_count);
    }

    /**
     *  @expectedException \InvalidArgumentException
     */

    public function testFromThrowsException() {
        $csv = new CSV\Query("not a valid argument");
        $this->fail("A not-valid filepath should throw a vanilla InvalidArgumentException");
    }

    public function testLimit() {
        $this->csv
             ->select("*")
             ->limit(1)
             ->execute()
             ;

        $count = -1;
        $file = fopen(self::OUTPUT_FILENAME, "r");
        while(fgetcsv($file)) { $count++; }

        $this->assertEquals(1, $count);
    }

    public function testSelect() {
        $rows = array("LOGINID", "DISPLAYNAME");
        $rowCount = count($rows);

        $this->csv
             ->select($rows)
             ->execute()
             ;

        $file = fopen(self::OUTPUT_FILENAME, "r");
        while($row = fgetcsv($file)) {
            $this->assertEquals($rowCount, count($row));
        }

        fclose($file);
    }

    public function testTransform() {        
        $rows = array("LOGINID", "DISPLAYNAME");
        $this->csv
             ->transform(function(&$row) {
                $row['LOGINID'] = "test-" . $row['LOGINID'];
             })
             ->execute()
             ;

        $file = fopen(self::OUTPUT_FILENAME, "r");

        // advance header-row :P
        fgetcsv($file);
        while($row = fgetcsv($file)) {
            $this->assertTrue((bool) preg_match("/^test\-/", $row[0]));
        }
    }
}