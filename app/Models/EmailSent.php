<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\EmailSentObserver;

class EmailSent extends BaseModel
{
    protected $table = 'email_sent';
    protected $fillable = ['status'];
    
    public static function boot() {
        parent::boot();
        EmailSent::observe(new EmailSentObserver());
    }
    
    public function contact() {
        return $this->belongsTo(Contact::class);
    }
    
    public function content() {
        return $this->belongsTo(Email::class, 'email_content_id');
    }
    
    public function track() {
        return $this->hasMany(EmailTracking::class);
    }
    
    public function communicationContent()
    {
        return $this->belongsTo(CommunicationContent::class);
    }
}
