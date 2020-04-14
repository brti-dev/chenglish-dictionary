<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Pced\Exception;

class PHPTest extends TestCase
{
    public function testReferences(): void
    {
        
        # Using a reference to permanently change the values in an array
        $bands = array("The Beatles", "Outkast", "Queen");
        $this->assertIsArray($bands);
        foreach ($bands as &$band) {
          $band = strtoupper($band);
        }
        $this->assertNotSame("Queen", "QUEEN");
        $this->assertSame("QUEEN", $bands[2]);

        # Pass reference to funtion
        function goodbye(&$greeting) {
          $greeting = "See you later";
        }
        $myVar = "Hi there";
        goodbye( $myVar );
        $this->assertEquals($myVar, "See you later");

    }

    public function testAnon():void
    {
        # ANONYMOUS FUNCTIONS #

        $make_foo = function(int $num) {
          return "You made " . $num . " foo" . ($num != 1 ? 's' : '');
        };
        $this->assertSame($make_foo(5), "You made 5 foos");

        # Instead of writing a function that will only be used minimally, make an anymous function
        $this->assertSame( 
            array_map( function( $name ) {
              return "Hello " . ucfirst( $name );
            }, array("bob")),
            array("Hello Bob")
        );

        # Make a custom sort eg. for an associative array with nested keys
        $games = array(
          array("title" => "The Legend of Zelda", "year_published" => 1986),
          array("title" => "Chrono Trigger", "year_published" => 1995),
          array("title" => "Super Mario Bros.", "year_published" => 1985),
        );
        usort($games, function($gameA, $gameB){
          $methodA = strcmp(strval($gameA['year_published']), strval($gameB['year_published'])); //Identical to:
          $methodB = ($gameA['year_published'] < $gameB['year_published']) ? -1 : 1;
          $this->assertSame($methodB, $methodA);
          return $methodA;
        });
        $this->assertSame($games[0]['title'], "Super Mario Bros.");

        # Similarly, make a closure:
        function getSortFunction($sort_key) {
          return function ($a, $b) use ($sort_key) {
            return strcmp($a[$sort_key], $b[$sort_key]);
          };
        }
        usort($games, getSortFunction("title"));
        $this->assertSame($games[0]['title'], "Chrono Trigger");
    }

    public function testException()
    {
        $this->expectException(Exception::class);
        throw new Exception ("I am an exception", 112);
    }

    public function testObjects()
    {
        $obj_a = new stdClass;
        $obj_b = new stdClass;
        $this->assertEquals($obj_a, $obj_b);
    }
}