<?php

namespace Tests\Unit;

use App\DataTables\ContactDataTable;
use Tests\TestCase;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class ContactDataTableTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
     public function testStateDefaults() 
     {
         // submitting with no options returns an array with 3 values, the uri for ContactDataTable, and default values for name and is_user_search
        $values = ContactDataTable::stateDefaults(); 
        // var_dump($values);
        $this->assertEquals(count($values),4);
        $this->assertTrue(array_key_exists('time',$values));
        $this->assertTrue(array_key_exists('uri',$values));
        $this->assertTrue(array_key_exists('name',$values));
        $this->assertTrue(array_key_exists('is_user_search',$values));
        $this->assertGreaterThan(0,$values['time']);
        $this->assertEquals($values['uri'],ContactDataTable::DEFAULT_URI);
        $this->assertEquals($values['name'],null);
        $this->assertEquals($values['is_user_search'],0);
        
        
        // submitting the false $always_set_time flag exlcudes the time 
        $values = ContactDataTable::stateDefaults([],false); 
        // var_dump($values);
        $this->assertEquals(count($values),3);
        $this->assertFalse(array_key_exists('time',$values));
        
        // submitting the time in the options sets the time
        $values = ContactDataTable::stateDefaults(['time'=>time()]); 
        // var_dump($values);
        $this->assertEquals(count($values),4);
        $this->assertTrue(array_key_exists('time',$values));
        
        // submitting with an array with keys is_user_search and name results in a correct state
        $values = ContactDataTable::stateDefaults(['name'=>'foo','is_user_search'=>1]); 
        // var_dump($values);
        $this->assertEquals(count($values),4);
        $this->assertTrue(array_key_exists('time',$values));
        $this->assertTrue(array_key_exists('uri',$values));
        $this->assertTrue(array_key_exists('name',$values));
        $this->assertTrue(array_key_exists('is_user_search',$values));
        $this->assertGreaterThan(0,$values['time']);
        $this->assertEquals($values['uri'],ContactDataTable::DEFAULT_URI);
        $this->assertEquals($values['name'],'foo');
        $this->assertEquals($values['is_user_search'],1);
        
        // submitting with a Request with  is_user_search and name results in a correct state
        $values = ContactDataTable::stateDefaults(new Request(['name'=>'foo','is_user_search'=>1])); 
        $this->assertEquals(count($values),4);
        $this->assertTrue(array_key_exists('time',$values));
        $this->assertTrue(array_key_exists('uri',$values));
        $this->assertTrue(array_key_exists('name',$values));
        $this->assertTrue(array_key_exists('is_user_search',$values));
        $this->assertGreaterThan(0,$values['time']);
        $this->assertEquals($values['uri'],ContactDataTable::DEFAULT_URI);
        $this->assertEquals($values['name'],'foo');
        $this->assertEquals($values['is_user_search'],1);
        
        // submitting with an array with keys contact_tags and contact_excluded_tags results in a correct state
        $values = ContactDataTable::stateDefaults(['search'=>['contact_tags'=>[1,2,3,5],'contact_excluded_tags'=>[4,6,10,16]]]); 
        // var_dump($values);
        $this->assertEquals(count($values),5);
        $this->assertTrue(array_key_exists('search',$values));
        $this->assertEquals(count($values['search']),2);
        // note that the check looks for string versions of values passed in
        $this->assertSame($values['search']['contact_tags'],['1','2','3','5']);
        $this->assertSame($values['search']['contact_excluded_tags'],['4','6','10','16']);
        
     }
}
