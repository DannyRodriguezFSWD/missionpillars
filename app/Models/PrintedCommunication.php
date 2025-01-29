<?php

namespace App\Models;

use App\Scopes\TenantScope;

use Illuminate\Database\Eloquent\Model;

class PrintedCommunication extends Model
{
    protected $table = 'communication_contact';
    
    public function newQuery()
    {
        $builder = parent::newQuery();

        $tenant_id = self::determineTenantId();
        // scope by communications.tenant_id
        $builder->whereHas('communication', function($query) use ($tenant_id) {
            $query->where('tenant_id',$tenant_id);
        });
        
        return $builder;
    }
    
    protected static function determineTenantId() {
        if(auth()->check()){//for authenticated users
            return auth()->user()->tenant_id;
        }
        
        return TenantScope::useTenantId();
    }

    
    public function communication() {
        return $this->belongsTo(Communication::class);
    }
    
    public function contact() {
        return $this->belongsTo(Contact::class);
    }
    
    public function content() {
        return $this->belongsTo(Communication::class);
    }
    
    public function communicationContent()
    {
        return $this->belongsTo(CommunicationContent::class);
    }
    
    /** Scopes **/
    
    /**
     * Filters transactions between specified start and end date. Note the end date includes up to the end of that date (e.g., 12/31/2018 includes 12/31/2018 11:59pm)
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  Array $between and array with two indices: Carbon $start, Carbon $end   
     * @return 
     */
    public function scopeBetween($query, $between) {
        extract($between);
        $query->whereBetween('updated_at', [$start->startOfDay(), $end->endOfDay()]);
    }
}
