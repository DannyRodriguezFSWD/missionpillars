<?php

namespace App\Traits;
use App\Models\Country;
/**
 *
 * @author josemiguel
 */
trait CountriesTrait {
    
    private $key = 'id';
    
    public function getCountries($fields = ['name']) {
        array_push($fields, $this->key);
        $dbCountries = Country::select($fields)->orderBy('order')->get();
        $countries = collect($dbCountries)->reduce(function($countries, $dbCountries){
            $countries[array_get($dbCountries, $this->key)] = array_get($dbCountries, 'name');
            return $countries;
        }, []);
        
        return $countries;
    }
    
    public function getCountriesAsArrayObjects($fields = ['name', 'iso_3166_2'], $iso = false) {
        array_push($fields, $this->key);
        $dbCountries = Country::select($fields)->orderBy('order')->get();
        $countries = collect($dbCountries)->reduce(function($countries, $dbCountries) use($iso){
            $country['label'] = array_get($dbCountries, 'name');
            if(!$iso){
                $country['value'] = array_get($dbCountries, $this->key);
            }
            else{
                $country['value'] = array_get($dbCountries, 'iso_3166_2');
            }
            
            $country['selected'] = false;
            array_push($countries, $country);
            return $countries;
        }, []);
        
        return $countries;
    }
    
    public function getCountriesAutocomplete($fields = ['name', 'iso_3166_2']) {
        array_push($fields, $this->key);
        $dbCountries = Country::select($fields)->orderBy('name')->get();
        $countries = collect($dbCountries)->reduce(function($countries, $dbCountries){
            $country['value'] = array_get($dbCountries, 'name');
            $country['data'] = array_get($dbCountries, $this->key);
            
            array_push($countries, $country);
            return $countries;
        }, []);
        
        $data = ['suggestions' => $countries];
        
        return $data;
    }
    
}
