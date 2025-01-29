<?php

namespace App\Observers;

use App\Models\Folder;
use App\Models\Purpose;
use App\Constants;
use App\Traits\AlternativeIdTrait;
use App\Traits\TagsTrait;

/**
 * Description of ChatOfAccountObserver
 *
 * @author josemiguel
 */
class PurposeObserver {
    use AlternativeIdTrait, TagsTrait;
    
    private $folder = null;
    /**
     * Creates new tag if not exists and assigns 
     * tag property to Purpose
     * @param Purpose $chart
     */
    public function created(Purpose $chart) {
        //$this->retrieveTag($chart);
        //if (!array_get($chart, 'tag')) {
            //$tag = $this->setTag($chart, array_get($this->folder, 'id'), true);
            $tag = $this->setTag($chart, array_get(Constants::TAG_SYSTEM, 'FOLDERS.CHART_OF_ACCOUNTS'), true);
            array_set($chart, 'tag', $tag);
            
            $fields = ['alt_id' => array_get($tag, 'id'), 'label' => array_get($tag, 'name'), 'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY];
            $this->alternativeIdCreate(array_get($tag, 'id'), get_class($tag), $fields);
        //}
    }

    public function updated(Purpose $chart) {
        $tag = $chart->tagInstance;
        if($tag){
            array_set($tag, 'name', array_get($chart, 'name', 'unrecognized Purpose'));
            $tag->update();
        }
    }
    
    public function deleted(Purpose $chart) {
        $tag = $chart->tagInstance;
        if($tag){
            $tag->delete();
        }
    }

    //add  update method

    /**
     * Assigns tag property to Purpose
     * @param Purpose $chart
     */
    public function retrieveTag(Purpose $chart) {
        $folder = Folder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.CHART_OF_ACCOUNTS'));
        $this->folder = $folder;
        $tagName = array_get($chart, 'name', 'unrecognized Purpose');
        $tag = $this->tagExists($tagName, array_get($folder, 'id'));
        /*
        $tag = Tag::where([
                    ['name', '=', array_get($chart, 'name')],
                    ['folder_id', '=', array_get($folder, 'id')],
                ])->first();
         * 
         */
        array_set($chart, 'tag', $tag);
    }

}
