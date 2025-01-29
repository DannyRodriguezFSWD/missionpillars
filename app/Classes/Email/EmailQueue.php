<?php
namespace App\Classes\Email;

use App\Models\Email;
use App\Models\EmailSent;
use App\Constants;
use App\Traits\DocumentsTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use \Illuminate\Support\Facades\Session;
/**
 * Description of EmailQueue
 *
 * @author josemiguel
 */
class EmailQueue {
    use DocumentsTrait;
    
    /**
     * use $args = ["subject" => "string", "content" => "string", "model" => $model]
     * @param App\Models\Contact $contact
     * @param Array $args
     */
    public static function set($contact, $args = [], $attachments = null) {
        $item = [];
        array_set($item, 'list_id', array_get($args, 'list_id'));
        array_set($item, 'from_name', array_get($args, 'from_name'));
        array_set($item, 'from_email', array_get($args, 'from_email'));
        array_set($item, 'subject', array_get($args, 'subject'));
        array_set($item, 'reply_to', array_get($args, 'reply_to', null));
        array_set($item, 'content', array_get($args, 'content'));
        array_set($item, 'relation_id', array_get($args, 'model.id'));
        array_set($item, 'relation_type', get_class(array_get($args, 'model')));
        array_set($item, 'tenant_id', array_get($contact, 'tenant_id'));
        array_set($item, 'queued_by', array_get($args, 'queued_by', 'default'));
        array_set($item, 'transaction_start_date', array_get($args, 'transaction_start_date', null));
        array_set($item, 'transaction_end_date', array_get($args, 'transaction_end_date', null));
        array_set($item, 'include_transactions', array_get($args, 'include_transactions', 0));
        array_set($item, 'cc_secondary', array_get($args, 'cc_secondary', null));
        array_set($item, 'include_public_link', array_get($args, 'include_public_link', null));
        array_set($item, 'uuid', Uuid::uuid4());
        array_set($item, 'created_at', Carbon::now());
        array_set($item, 'updated_at', Carbon::now());
        array_set($item, 'email_editor_type', array_get($args, 'email_editor_type', 'tiny'));
        
        if(auth()->check()){
          array_set($item, 'created_by', array_get(auth()->user(), 'id'));
          array_set($item, 'created_by_session_id', Session::getId());
        }
        
        $id = DB::table('email_content')->insertGetId($item);
        
        if ($attachments) {
            (new self())->duplicateDocuments($attachments, $id, \App\Models\Communication::class);
        }
        
        if( !is_null($id) && $id > 0 ){
            $email = Email::withoutGlobalScopes()->where('id', $id)->first();
            
            $communicationContent = [
                'tenant_id' => array_get($contact, 'tenant_id'),
                'subject' => array_get($email, 'subject'),
                'content' => array_get($email, 'content'),
                'editor_type' => array_get($email, 'email_editor_type'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            $communicationContentId = DB::table('communication_contents')->insertGetId($communicationContent);
            
            self::queue($contact, $email, $communicationContentId);
        }
    }
    
    public static function queue($contact, $email, $communicationContentId = null) {
        $track_and_tag = array_get($email, 'track_and_tag_events', '[]');
        if (is_string($track_and_tag)) $track_and_tag = json_decode($track_and_tag, true);
        
        if(array_has($track_and_tag, 'sent')){
            $contact->tags()->sync(array_get($track_and_tag, 'sent'), false);
        }
        
        $sent = [];
        array_set($sent, 'contact_id', array_get($contact, 'id'));
        array_set($sent, 'email_content_id', array_get($email, 'id'));
        array_set($sent, 'uuid', Uuid::uuid4());
        array_set($sent, 'sent_at', Carbon::now());
        array_set($sent, 'created_at', Carbon::now());
        array_set($sent, 'updated_at', Carbon::now());
        array_set($sent, 'message', array_get(Constants::NOTIFICATIONS, 'EMAIL.MESSAGE.QUEUED'));
        array_set($sent, 'tenant_id', array_get($contact, 'tenant_id'));
        array_set($sent, 'communication_content_id', $communicationContentId);
        
        if(auth()->check()){
          array_set($sent, 'created_by', array_get(auth()->user(), 'id'));
          array_set($sent, 'created_by_session_id', Session::getId());
        }
        
        $id = DB::table('email_sent')->insertGetId($sent);
    }
    
}
