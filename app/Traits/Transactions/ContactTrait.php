<?php

namespace App\Traits\Transactions;

use App\Constants;
use App\Models\Contact;
use App\Models\Address;
use App\Models\User;

/**
 *
 * @author josemiguel
 */
trait ContactTrait {

  /**
   * Saves Contact, Address and ContactAltId
   * @param JSON $person
   * @return Contact
   */
   public function saveContactData($jPerson = null, $tags = []) {
       if (is_object($jPerson)) $jPerson = (array) $jPerson;
       
       if (!$jPerson) return null;
       
       $alt_id = array_get($jPerson, 'alt_id', 0);
       $altIdObject = $this->alternativeIdRetrieve($alt_id, Contact::class);
       
       if (!$alt_id || !$altIdObject) { // Either null/zero alt_id or first sync 
           // check if contact exists by email
           $contact = Contact::whereNotNull('email_1')
           ->where('email_1', '!=', '')
           ->where('email_1', array_get($jPerson, 'email_1'))->first();
           
            // TODO if reenabled, consider matching by first_name, last_name and something else (e.g., phone number?)
            // This was re-enabled because of point of sale donations not being mapped to any contact (in this case the alt_id is 0)
            // See Asana https://app.asana.com/0/inbox/1192002100008023
            if (is_null($contact) && !$alt_id) {
                // check if contact exists by name
                $contact = Contact::where([
                    ['first_name', '=', array_get($jPerson, 'first_name')],
                    ['last_name', '=', array_get($jPerson, 'last_name')]
                ])->first();
            }
           
            // if contact is still null, create it
            if (is_null($contact)) {
                $contact = mapModel(new Contact(), $jPerson);
               
                if (array_get($jPerson, 'sub_type') === 'organizations') {
                    $contact->type = 'organization';
                    $contact->company = array_get($jPerson, 'prefered_name');
                }
               
                auth()->user()->tenant->contacts()->save($contact);
            }
            // TODO consider whether matched info warrants an update and adding a flag on the tenant indicating MP contact should be updated
            // We update only values that are null or empty in the database
            else{
                mapModelIfEmpty($contact, $jPerson);
                $contact->update();
            }
           
           $this->tagContact($contact, $tags);
           $this->saveContactAddresses($contact, $jPerson);
           
           if ($alt_id) { // alt_id is not null/0, create related alt id object
               $fields = [
                   'alt_id' => $alt_id,
                   'label' => array_get($jPerson, 'first_name'),
                   'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY
               ];
               $this->alternativeIdCreate(array_get($contact, 'id'), get_class($contact), $fields);
           }
           
           return $contact->refresh();
       } 
       
       
       $contact = array_get($altIdObject, 'getRelationTypeInstance');
       if(is_null($contact)){ 
           // check if it was deleted
           $contact = Contact::withTrashed()->where('id', array_get($altIdObject, 'relation_id'))->first();
           if(!empty(array_get($contact, 'deleted_at'))){ // if soft deleted
               array_set($contact, 'deleted_at', null); // undelete it
           }
       }
       
       
       if(!is_null($contact)){ // finally we add or update extra data
           // TODO consider adding a flag on the tenant indicating MP contact should be updated
           // We update only values that are null or empty in the database
           mapModelIfEmpty($contact, $jPerson);
           $contact->update();
           
           $this->tagContact($contact, $tags);
           $this->saveContactAddresses($contact, $jPerson);
       }
       
       return $contact->refresh();
   }

  public function saveContactAddresses($contact, $jperson) {
    $root = array_get($jperson, 'addresses.data');

    if ($root) {
      foreach ($root as $item) {
        $data = array_get($item, 'data');
        $altIdObject = $this->alternativeIdRetrieve(array_get($data, 'alt_id'), Address::class);
        if (!$altIdObject) {
          $address = mapModel(new Address(), $data);
          array_set($address, 'relation_id', array_get($contact, 'id'));
          array_set($address, 'relation_type', get_class($contact));

          if (auth()->user()->tenant->addresses()->save($address)) {
            $fields = ['alt_id' => array_get($data, 'alt_id'), 'label' => array_get($jperson, 'first_name'), 'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY];
            $this->alternativeIdCreate(array_get($address, 'id'), get_class($address), $fields);
          }
        } else {
          $address = $altIdObject->getRelationTypeInstance;
          $data = array_get($item, 'data');
          mapModel($address, $data);
          

          try {
            if (is_array($address) || is_null($address)) {
              $msg = "is array: " . is_array($address) . '. is null: ' . is_null($address);
              \App\Classes\MissionPillarsLog::exception(new \Exception($msg), json_encode($altIdObject));
            } else {
              $address->update();
            }
          } catch (\Exception $ex) {
            \App\Classes\MissionPillarsLog::exception($ex, json_encode($address));
          } catch (\Symfony\Component\Debug\Exception\FatalThrowableError $ex) {
            \App\Classes\MissionPillarsLog::exception($ex, json_encode($address));
          } catch (\Symfony\Component\Debug\Exception\FatalErrorException $ex) {
            \App\Classes\MissionPillarsLog::exception($ex, json_encode($address));
          } catch (\Psy\Exception\FatalErrorException $ex) {
            \App\Classes\MissionPillarsLog::exception($ex, json_encode($address));
          }
        }
      }
    }
  }

  /**
   * 
   * @param App\Models\Contact $contact
   * @param Array $tags
   */
  public function tagContact($contact = null, $tags = null) {
    $tags_filtered = array_filter($tags, function($value) {
      return !is_null($value);
    });
    if ($contact && $tags) {
      try {
        $contact->tags()->sync($tags_filtered, false);
      } catch (\Illuminate\Database\QueryException $ex) {
        \App\Classes\MissionPillarsLog::exception($ex, json_encode($tags));
      }
    }
  }

  public function saveUserData($jPerson = null) {
    if ($jPerson && array_has($jPerson, 'alt_user_id') && array_has($jPerson, 'alt_user_username')) {
      $altUserId = $this->alternativeIdRetrieve(array_get($jPerson, 'alt_user_id', 0), User::class);

      if (!$altUserId) {
        //$user = User::where('email', array_get($jPerson, 'alt_user_username'))->first();
        $user = User::where([
            ['name', '=', array_get($jPerson, 'first_name')],
            ['last_name', '=', array_get($jPerson, 'last_name')]
          ])->first();
      } else {
        $user = $altUserId->getRelationTypeInstance;
      }

      if (is_null($user)) {
        //if email is empty or  it is not a valid email, then do not create user
        if (is_null(array_get($jPerson, 'alt_user_username')) || !filter_var(array_get($jPerson, 'alt_user_username'), FILTER_VALIDATE_EMAIL)) {
          return null;
        }

        $user = mapModel(new User(), $jPerson);

        array_set($user, 'name', array_get($jPerson, 'first_name'));
        array_set($user, 'last_name', array_get($jPerson, 'last_name'));
        array_set($user, 'email', array_get($jPerson, 'alt_user_username'));
        array_set($user, 'password', bcrypt(str_random()));

        auth()->user()->tenant->users()->save($user);
        $role = \App\Models\Role::where('name', 'organization-contact')->first();
        $user->attachRole($role);

        $fields = ['alt_id' => array_get($jPerson, 'alt_user_id'), 'label' => array_get($jPerson, 'first_name'), 'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY];
        $this->alternativeIdCreate(array_get($user, 'id'), get_class($user), $fields);

        $altContactId = $this->alternativeIdRetrieve(array_get($jPerson, 'alt_user_id', 0), Contact::class);
        $contact = $altContactId ? $altContactId->getRelationTypeInstance : null;
        if ($contact) {
          array_set($contact, 'user_id', array_get($user, 'id'));
          $contact->update();
        }

        return $user;
      } else {
        if (array_get($user, 'email') === array_get($jPerson, 'alt_user_username')) {
          mapModel($user, $jPerson);
          $user->update();
        }
      }

      return $user;
    }
    return null;
  }

  /**
   * Stores contact Address
   * @param App\Models\Contact $contact
   * @param Array $jaddress
   * @return App\Models\Address
   */
  public function saveContactAddress($contact, $jaddress = null) {
    $address = array_get($contact, 'address');
    if ($address) {
      $address = mapModel($address, $jaddress);
      $address->update();
    } else {
      $address = mapModel(new Address(), $jaddress);
      array_set($address, 'contact_id', array_get($contact, 'id'));
      auth()->user()->tenant->addresses()->save($address);
    }
    return $address;
  }

  public function setSingleContact($jPerson = null, $tags = []) {
    if ($jPerson) {
      $altIdObject = $this->alternativeIdRetrieve(array_get($jPerson, 'alt_id', 0), Contact::class);
      $contact = null;
      if (!$altIdObject) {
        if (!is_null(array_get($jPerson, 'email_1')) && !is_null(array_get($jPerson, 'first_name')) && !is_null(array_get($jPerson, 'last_name'))) {
          $contact = mapModel(new Contact(), $jPerson);
          auth()->user()->tenant->contacts()->save($contact);
          $this->tagContact($contact, $tags);
          $fields = ['alt_id' => array_get($jPerson, 'alt_id'), 'label' => array_get($jPerson, 'first_name'), 'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY];
          $this->alternativeIdCreate(array_get($contact, 'id'), get_class($contact), $fields);
        }
      } else {
        if (!is_null(array_get($jPerson, 'email_1')) && !is_null(array_get($jPerson, 'first_name')) && !is_null(array_get($jPerson, 'last_name'))) {
          $contact = array_get($altIdObject, 'getRelationTypeInstance');
          mapModel($contact, $jPerson);
          $contact->update();
          $this->tagContact($contact, $tags);
        }
      }
      return $contact;
    }
    return null;
  }
    
}
