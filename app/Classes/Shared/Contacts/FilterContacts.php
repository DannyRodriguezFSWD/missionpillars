<?php

namespace App\Classes\Shared\Contacts;
use App\Models\Contact;

use App\Constants;
use App\Models\Lists;
/**
 * Description of Chart
 *
 * @author josemiguel
 */
class FilterContacts {

    public static function byList($list) {
        if(is_numeric($list)){//just the id so get the list from id
            $list = Lists::find($list);
        }
        $in = array_get($list, 'inTags', []);
        $out = array_get($list, 'notInTags', []);
        $contacts = self::byTags($in, $out);
        return $contacts;
    }

    public static function byTags($in = [], $out = []) {
        if(is_null($in)){
            $in = [];
        }
        else if(is_string($in)){//comma separated string, make it array
            $in = explode(',', $in);
        }
        else{
            $in = array_pluck($in, 'id');
        }
        
        if(is_null($out)){
            $out = [];
        }
        else if(is_string($out)){//comma separated string, make it array
            $out = explode(',', $out);
        }
        else{
            $out = array_pluck($out, 'id');
        }
        
        $contacts_in = Contact::whereHas('tags', function($q) use($in){
            $q->whereIn('id', $in);
        })->get();

        $contacts_out = Contact::whereHas('tags', function($q) use($out){
            $q->whereIn('id', $out);
        })->get();
        
        $search = array_diff(array_pluck($contacts_in, 'id'), array_pluck($contacts_out, 'id'));
        
        $contacts = Contact::whereIn('id', $search)->get();
        
        return $contacts;
    }

    public static function byListTagsSystem($list, $in, $out){
        $contacts_in_lists = self::byList($list);
        $contacts_in_tags = self::byTags($in, $out);

        $use_contacts_in_tags = false;
        if(!is_null($in) || !is_null($out)){
            $use_contacts_in_tags = true;
        }
        
        $builder = Contact::whereIn('id', array_pluck($contacts_in_lists, 'id'));
        if($use_contacts_in_tags){
            $builder->whereIn('id', array_pluck($contacts_in_tags, 'id'));
        }
        $contacts = $builder->get();
        
        return $contacts;
    }

}
