<?php

namespace App\Traits;

use App\Models\AltId;

/**
 *
 * @author josemiguel
 */
trait AlternativeIdTrait {

    /**
     * Creates a new Alternative Id
     * @param int $id
     * @param string (fully qualified classname) $class
     * @param array $fields
     * @return AltId | null
     */
    public function alternativeIdCreate($id, $class, $fields = []) {
        $alt = mapModel(new AltId(), $fields);
        array_set($alt, 'relation_id', $id);
        array_set($alt, 'relation_type', $class);

        if (auth()->user()->tenant->altIds()->save($alt)) {
            return $alt;
        }

        return null;
    }

    /**
     * Retrievess Alternative id record
     * @param string (fully qualified classname) $class
     * @param int $altId
     * @return AltId | null
     */
    public function alternativeIdRetrieve($altId, $class) {
        return AltId::where([
                    ['alt_id', '=', $altId],
                    ['relation_type', '=', $class]
                ])->first();
    }

    /**
     * 
     * @param int $altId
     * @param string (fully qualified classname) $class
     * @return boolean
     */
    public function alternativeIdExists($altId, $class) {
        $alt = $this->getAlternativeId($altId, $class);
        return $alt ? true : false;
    }
    
    public function createAltId($system) {
        $alt = new AltId();
        array_set($alt, 'relation_id', array_get($this, 'id'));
        array_set($alt, 'relation_type', get_class($this));
        array_set($alt, 'alt_id', array_get($this, 'id'));
        array_set($alt, 'label', array_get($this, 'category'));
        
        array_set($alt, 'system_created_by', $system);
        
        
        if (auth()->user()->tenant->altIds()->save($alt)) {
            return true;
        }

        return false;
        
    }

}
