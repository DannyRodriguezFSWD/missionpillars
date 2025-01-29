<?php

namespace App\Observers;

use App\Models\Tag;
use App\Models\Form;
use App\Constants;
use App\Traits\TagsTrait;

/**
 * Description
 *
 * @author josemiguel
 */
class FormsObserver {
    use TagsTrait;
    
    
    public function created(Form $form) {
        $folderId = array_get(Constants::TAG_SYSTEM, 'FOLDERS.FORMS');
        $tagName = str_replace(':name:', array_get($form, 'name'), array_get(Constants::TAG_SYSTEM, 'TAGS.FORM'));
        
        if (!$this->tagExists($tagName, $folderId)) {
            $this->setTag($form, $folderId, true, $tagName);
        }
        else{
            $tagName .= '-' . array_get($form, 'id');
            $this->setTag($form, $folderId, true, $tagName);
        }
    }
    
    public function updated(Form $form) {
        $tagName = str_replace(':name:', array_get($form, 'name'), array_get(Constants::TAG_SYSTEM, 'TAGS.FORM'));
        $tag = $form->tagInstance;
        if($tag){
            array_set($tag, 'name', $tagName);
            $tag->update();
        }
    }
    
    public function deleted(Form $form) {
        $tag = $form->tagInstance;
        if($tag){
            $tag->delete();
        }
    }
}
