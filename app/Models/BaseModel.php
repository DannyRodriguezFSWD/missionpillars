<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use App\Scopes\TenantScope;
use App\Traits\Subdomains;
use App\Classes\MissionPillarsLog;

/**
 * Description of BaseModel
 *
 * @author josemiguel
 */
class BaseModel extends Model {
    use Subdomains;
    protected $guarded = ['id'];
    protected $auto_save_tenant_id = false;
    
    protected static function boot() {
        parent::boot();
        static::addGlobalScope(new TenantScope);
    }
    
    /** Scopes **/
    
    public function scopeNoTenantScope($query) {
        return $query->withoutGlobalScope(TenantScope::class);
    }
    
    public function scopeGlobal($query) {
        return $query->whereNull('tenant_id');
    }
    
    

    public function save(array $options = []) {
        if ($this->auto_save_tenant_id && auth()->check() && !$this->tenant_id) {
            $this->tenant_id = auth()->user()->tenant_id;
        } 
        
        if ($this->validate()) {
            return parent::save($options);
        }
        return false;
    }

    public function delete() {
        if ($this->validate()) {
            return parent::delete();
        }
        return false;
    }

    private function validate() {
        if($usetenantid = TenantScope::useTenantId()) {
            if (array_key_exists('tenant_id', $this->attributes) && $this->attributes['tenant_id'] == $usetenantid) {
                return true;
            }
        }
        elseif(auth()->check()){//for authenticated users
            if (array_key_exists('tenant_id', $this->attributes) && $this->attributes['tenant_id'] == auth()->user()->tenant_id) {
                return true;
            }
        }
        else{ //for public routes
            $url = \Illuminate\Support\Facades\Request::getHost();
            $subdomain = $this->getSubdomain($url);
            $tenant = $this->getTenant($subdomain);
            if( $tenant && array_key_exists('tenant_id', $this->attributes) && $this->attributes['tenant_id'] == array_get($tenant, 'id')  ){
                return true;
            }
        }
        
        return false;
    }

    /**
     * Overriddes default getAttributes function
     * $default param indicates if should perform default function or custom
     * @param boolean $default
     * @return array
     */
    public function getAttributes($default = true) {
        if ($default) {
            return parent::getAttributes();
        }
        return Schema::getColumnListing($this->getTable());
    }

    /**
     * Gets all records from AltIds table corresponding to relationship type
     * and query
     * util when many tables has alt ids
     * @return Array AltId | null
     */
    public function getAltIds() {
        MissionPillarsLog::deprecated();
        return $this->morphMany(AltId::class, 'relation');
    }
    
    /**
     * Gets all records from AltIds table corresponding to relationship type
     * and query
     * util when many tables has alt ids
     * @return Array AltId | null
     */
    public function altIds() {
        return $this->morphMany(AltId::class, 'relation');
    }

    public function documents() 
    {
        return $this->morphMany(Document::class, 'relation');
    }
    
    /**
     * TODO deprecate
     * Returns instace of relation_type value from  model (if column exists)
     * @return relation_type Model | null
     */
    public function getRelationTypeInstance() {
        return $this->morphTo('relation');
    }
    
    /**
     * TODO deprecate
     * Returns instace of relation_type value from  model (if column exists)
     * @return relation_type Model | null
     */
    public function getRelationTypeInstanceWithTrashed() {
        return $this->morphTo('relation')->withTrashed();
    }
    
    /**
     * Returns instace of relation_type value from model (if column exists)
     * @return relation_type Model | null
     */
    public function relatedModel() {
        return $this->morphTo('relation');
    }
    
    /**
     * returns the automatically generated tag for the model, if it exists
     */
    public function autoTag() {
        return $this->morphOne(Tag::class, 'relation');
    }
    
    /**
     * Returns a collection of tags associated with the model.
     * @param  [string] $key Optional. If specified, limits the tags to the specified key
     * @return relation_type Model | null
     */
    public function tags($key = null)
    {
        $relation = $this->morphToMany(Tag::class, 'taggable')
        ->withTimestamps()->withPivot('key');
        
        if ($key) {
            if (is_array($key)) $relation->wherePivotIn('key',$key);
            else $relation->wherePivot('key',$key);
        }
        
        return $relation;
    }
    
    /**
     * Safely sync tags based on the specified (or unspecified) key leaving others unaffected
     * @param  [array] $values An array of integers representing valid Tag ids
     * @param  [string] $key   Optional. If specified, tags will be added with the specified keys
     * @return 
     */
    public function syncTags($values, $key = null)
    {
        if ($key) { // If key isn't specified only sync non-keyed tags
            $values = array_fill_keys($values, ['key'=>$key]);
        } 
        $this->tags()->wherePivot('key',$key)->detach();
        
        return $this->tags()->attach($values);
    }
    
    /**
     * Allows setting unkeyed tags using equals (e.g., $object->tags = [1,2,3])
     * @param  [array] $values An array of integers representing valid Tag ids
     */
    public function setTagsAttribute($values)
    {
        return $this->syncTags($values);
    }
    
    
    /**
     * Returns Tag
     * Deprecated. Additional tags should use the tags relationship
     * @return Tag | null
     */
    public function tagInstance() {
        return $this->hasOne(Tag::class, 'relation_id', 'id')->where('relation_type', get_class($this));
    }
    
    public function addressInstance() {
        return $this->addresses();
    }
    
    /**
     * Since we are using is_maling as primary we want to show those first
     * 
     * @return type
     */
    public function orderedAddresses()
    {
        return $this->addresses()->orderBy('is_mailing', 'desc')->orderBy('id');
    }
    
    /**
     * Get all addresses attached to current model
     * @return Array Address
     */
    public function addresses() {
        return $this->morphMany(Address::class, 'relation');
    }

    /**
     * Executes custom events
     * @param type $event
     * @param type $halt
     */
    public function fireEvent($event, $halt = true) {
        $this->fireModelEvent($event, $halt);
    }
    
    /**
     * every model can have notes
     */
    public function notes() {
      return $this->morphMany(Note::class, 'relation')->orderBy('date', 'desc')->orderBy('id', 'desc');
    }

    public function createdFromC2G()
    {
        return !empty($this->altIds()->where('system_created_by', 'Continue to Give')->count());
    }

}
