<?php

namespace App\Observers;

use App\Models\Folder;
use App\Models\Tag;
use App\Models\Campaign;
use App\Constants;
use App\Traits\AlternativeIdTrait;
use App\Traits\TagsTrait;

/**
 * Description of ChatOfAccountObserver
 *
 * @author josemiguel
 */
class CampaignsObserver {
    use AlternativeIdTrait, TagsTrait;
    
    private $folder = null;
    /**
     * Creates new tag if not exists and assigns 
     * tag property to Purpose
     * @param Campaign $campaign
     */
    public function created(Campaign $campaign) {
        $this->retrieveTag($campaign);
        if (!array_get($campaign, 'tag')) {
            $tag = $this->setTag($campaign, array_get($this->folder, 'id'), true);
            array_set($campaign, 'tag', $tag);
            
            $fields = ['alt_id' => array_get($tag, 'id'), 'label' => array_get($tag, 'name'), 'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY];
            $this->alternativeIdCreate(array_get($tag, 'id'), get_class($tag), $fields);
        }
    }
    
    public function updated(Campaign $campaign) {
        $tag = $campaign->tagInstance;
        if(!is_null($tag)){
            array_set($tag, 'name', array_get($campaign, 'name'));
            $tag->update();
        }
    }
    
    public function deleted(Campaign $campaign) {
        $tag = $campaign->tagInstance;
        $tag->delete();
    }

    /**
     * Assigns tag property to Purpose
     * @param Purpose $campaign
     */
    public function retrieveTag(Campaign $campaign) {
        $folder = Folder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.CAMPAIGNS'));
        
        $this->folder = $folder;
        $tag = Tag::where([
                    ['name', '=', array_get($campaign, 'name')],
                    ['folder_id', '=', array_get($folder, 'id')],
                ])->first();
        array_set($campaign, 'tag', $tag);
    }

}
