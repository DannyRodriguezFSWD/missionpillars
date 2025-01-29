<?php

namespace App\Traits;

use App\Models\Address;
use App\Models\Contact;
use App\Models\Country;

trait AddressTrait {
    protected $address_unique_columns = [
            'mailing_address_1',
            'mailing_address_2',
            'p_o_box',
            'city',
            'region',
            // 'country',
            'postal_code',
            'country_id',
        ];
    
    /**
     * Makes a new valid address from array, if possible
     * @param  array  $address 
     * @return null|Address          A valid address or null if address specified is not valid
     */
    public function newValidAddress(array $address) {
        $address = array_filter($address);
        $address =  mapModel(new Address, $address);
        if (!$this->isValidAddress($address)) return null;
        $country = Country::find($address->country_id ?: $address->country);
        if ($country) {
            $address->country_id = $country->id;
            $address->country = $country->iso_3166_2;
        }
        
        return $address;
    }
    
    /**
     * If valid, finds an existing address linked to the contact, or creates one saving it to the database. 
     * @param  array  $address 
     * @return null|Address          A valid address from the database or null if address specified is not valid
     */
    public function findOrCreateValidAddress(Contact $contact, array $address) {
        $address = $this->findOrNewValidAddress($contact, $address);
        if (!$address) return null;
        
        if(!$address->relation_id){
            $address->tenant_id = $contact->tenant_id;
            $contact->addresses()->save($address);
        }
        else $address->save();
        
        return $address->refresh();
    }
    
    /**
     * If valid, finds an existing address linked to the contact, or creates one saving it to the database. 
     * @param  array  $address 
     * @return null|Address          A valid address from the database or null if address specified is not valid
     */
    public function findOrNewValidAddress(Contact $contact, array $address) {
        $address = $this->newValidAddress($address);
        if (!$address) return null;
        
        $existing_address = $contact->addresses();
        
        foreach ($this->address_unique_columns as $column) {
            if (!$address->$column) continue; // skip checking columns that aren't set
            
            $existing_address->where(function ($query) use ($address, $column) {
                // this check allows matching addresses with less than the specified columns
                $query->whereIn($column, ['',$address->$column]);
                
                if ($column == 'city' && !$address->postal_code) $query->whereNull('postal_code'); // e.g., Can't add Philadelphia -- 19104 -- to 18463
                else $query->orWhereNull($column);
                
                if ($column == 'postal_code' && !$address->city) $query->whereNull('city'); // e.g., Can't add 19104 -- Phliadelphia -- to Jefferson Township
                else $query->orWhereNull($column);
            });
        }
        
        if ($existing_address->count()) {
            $existing_address =  $existing_address->first();
            $address = $existing_address->fill($address->toArray());
        }
        
        return $address;
    }
    
    /**
     * Determines is an address is 'valid'
     * @param  Address $address 
     * @return boolean          Returns true if address has a mailing address or both a city and region (state) and false otherwise.
     */
    public function isValidAddress(Address $address) {
        return $address->mailing_address_1 || ($address->city && $address->region);
    }
    
    /**
     * Checks if an address is unique against the contacts current addresses
     * @param  Contact $contact 
     * @param  Address $address
     * @return boolean          Returns true if no addresses match the submitted address, false otherwise
     */
    public function isUniqueAddress(Contact $contact, Address $address) {
        $values = array_filter($address->toArray());
        $values = array_only($values, $this->$address_unique_columns);
        
        return $contact->addresses()->where($values)->count() == 0;
    }
}
