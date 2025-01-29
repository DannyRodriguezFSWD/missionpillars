<?php

namespace App\Observers;

use App\Models\CalendarEvent;
use App\Constants;
use App\Traits\TagsTrait;

/**
 * Description
 *
 * @author josemiguel
 */
class CalendarEventsObserver {
    use TagsTrait;
    
    public function created(CalendarEvent $event) {
        $folderId = array_get(Constants::TAG_SYSTEM, 'FOLDERS.EVENTS');
        $tagName = str_replace(':name:', array_get($event, 'name'), array_get(Constants::TAG_SYSTEM, 'TAGS.EVENT'));
        
        if (!$this->tagExists($tagName, $folderId)) {
            $this->setTag($event, $folderId, true, $tagName);
        }
    }
    
    public function updated(CalendarEvent $event) {
        $tagName = str_replace(':name:', array_get($event, 'name'), array_get(Constants::TAG_SYSTEM, 'TAGS.EVENT'));
        $tag = $event->tagInstance;
        if($tag){
            array_set($tag, 'name', $tagName);
            $tag->update();
        }
    }
    
    public function deleted(CalendarEvent $event) {
        $tag = $event->tagInstance;
        if($tag){
            $tag->delete();
        }
    }
}
