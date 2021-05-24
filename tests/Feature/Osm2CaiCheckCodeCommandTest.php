<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class Osm2CaiCheckCodeCommandTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testLOk() { $this->_tc('L',0);}
    public function testLPIOk() { $this->_tc('LPI',0);}
    // TODO: public function testLPIOOk() { $this->_tc('LPIO',0);}
    // TODO: public function testLPIO1Ok() { $this->_tc('LPIO1',0);}
    public function testBadSyntax() { $this->_tc('12',1);}
    public function testBadSyntax2() { $this->_tc('123456',1);}
    public function testNotExistingCodeRegion() { $this->_tc('X',2);}
    public function testNotExistingCodeProvince() { $this->_tc('XXX',2);}
    public function testNotExistingCodeArea() { $this->_tc('XXXX',2);}
    public function testNotExistingCodeSector() { $this->_tc('XXXXX',2);}


    private function _tc($code,$expectedExitCode){
        $p=['code'=>$code];
        $this->artisan('osm2cai:check_code',$p)->assertExitCode($expectedExitCode);
    }
}
