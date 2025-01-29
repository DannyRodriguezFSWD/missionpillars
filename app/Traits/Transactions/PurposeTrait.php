<?php

namespace App\Traits\Transactions;

use App\Constants;
use App\Models\Purpose;
use Illuminate\Support\Facades\Log;

/**
 *
 * @author josemiguel
 */
trait PurposeTrait {
    /**
     * Saves Purpose
     * @param Array $jChart
     * @return \App\Models\Purpose
     */
    public function savePurposeData($jChart = null, $jdata = null) {
        if( is_null(array_get($jChart, 'name')) ){
            $purpose = Purpose::whereNull('tenant_id')->first();
            if(!is_null($purpose)){
                $purpose->fireEvent('retrieveTag');
            }
            
            Log::info('C2G full transaction: ', ['transaction' => json_encode($jdata)]);
            Log::info('Continue To Give: ', ['chart' => json_encode($jChart)]);
            Log::info('Mission Pillars: ', ['chart' => json_encode($purpose)]);
                    
            return $purpose;
        }
        
        if ($jChart) {
            $altId = $this->alternativeIdRetrieve(array_get($jChart, 'alt_id', 0), Purpose::class);
            
            if (!$altId) {
                $purpose = mapModel(new Purpose(), $jChart);
                
                if (array_get($jChart, 'sub_type') === Constants::CHART_OF_ACCOUNT_SUBTYPE_MISSIONARY) {
                    array_set($purpose, 'type', Constants::CHART_OF_ACCOUNT_SUBTYPE_MISSIONARY);
                } elseif (array_get($jChart, 'sub_type') === Constants::CHART_OF_ACCOUNT_SUBTYPE_ORGANIZATIONS) {
                    array_set($purpose, 'type', 'organization');
                } else {
                    array_set($purpose, 'type', Constants::CHART_OF_ACCOUNT_SUBTYPE_PURPOSE);
                }
                if (auth()->user()->tenant->purposes()->save($purpose)) {
                    $fields = ['alt_id' => array_get($jChart, 'alt_id'), 'label' => array_get($jChart, 'name'), 'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY];
                    $this->alternativeIdCreate(array_get($purpose, 'id'), get_class($purpose), $fields);
                    return $purpose;
                }
            }
            $purpose = $altId->getRelationTypeInstance()->withTrashed()->first();
            if(!is_null($purpose)){
                $purpose->fireEvent('retrieveTag');
            }
            return $purpose;
        }
        return null;
    }
    
    public function setMissionary($person, $purposeTo, $purposeFor) {
        if ($person) {
            $tags = [
                array_get($purposeTo, 'tag.id'),
                array_get($purposeFor, 'tag.id'),
                array_get(Constants::TAG_SYSTEM, 'TAGS.MISSIONARY')
            ];
            
            $missionary = $this->saveContactData($person, $tags);

            array_set($purposeTo, 'contact_id', array_get($missionary, 'id', null));
            $purposeTo->update();

            array_set($purposeFor, 'contact_id', array_get($missionary, 'id', null));
            $purposeFor->update();
        }
    }
    
    public function setCampaign($person, $purposeTo, $purposeFor, $throughCampaign) {
        $fundraiser = null;
        if ($person) {
            $tags = [
                array_get($purposeTo, 'tag.id'),
                array_get($purposeFor, 'tag.id'),
                array_get(Constants::TAG_SYSTEM, 'TAGS.FOUNDRISER'),
            ];
            $fundraiser = $this->saveContactData($person, $tags);
        }

        $campaign = $this->saveCampaignData($throughCampaign, $purposeFor, $fundraiser);

        if ($fundraiser) {
            $tags = [array_get($campaign, 'tag.id')];
            $this->tagContact($fundraiser, $tags);
        }
        
        return $campaign;
    }
    
    public function setSinglePurpose($jChart) {
        if($jChart){
            $altId = $this->alternativeIdRetrieve(array_get($jChart, 'alt_id', 0), Purpose::class);
            if (is_null($altId)) {
                $purpose = mapModel(new Purpose(), $jChart);
            }
            else{
                $purpose = $altId->getRelationTypeInstance()->withTrashed()->first();
            }
            
            if (array_get($jChart, 'sub_type') === Constants::CHART_OF_ACCOUNT_SUBTYPE_MISSIONARY) {
                array_set($purpose, 'type', Constants::CHART_OF_ACCOUNT_SUBTYPE_MISSIONARY);
            } elseif (array_get($jChart, 'sub_type') === Constants::CHART_OF_ACCOUNT_SUBTYPE_ORGANIZATIONS) {
                array_set($purpose, 'type', 'organization');
            } else {
                array_set($purpose, 'type', Constants::CHART_OF_ACCOUNT_SUBTYPE_PURPOSE);
            }
            
            if (is_null($altId)) {
                auth()->user()->tenant->purposes()->save($purpose);
                $fields = ['alt_id' => array_get($jChart, 'alt_id'), 'label' => array_get($jChart, 'name'), 'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY];
                $this->alternativeIdCreate(array_get($purpose, 'id'), get_class($purpose), $fields);
            }
            else{
                mapModel($purpose, $jChart);
                $purpose->is_active = array_get($jChart, 'receive_donations', 1);
                $purpose->update();
                
                if (array_get($jChart, 'deleted_at')) {
                    $this->deleteRelatedCampaigns($purpose);
                }
            }
            $purpose->fireEvent('retrieveTag');
            
            return $purpose;
        }
        
        $purpose = Purpose::whereNull('tenant_id')->first();
        $purpose->fireEvent('retrieveTag');
        return $purpose;
    }
    
    public function setSingleMissionary($person, $purposeTo, $purposeFor) {
        if ($person) {
            $tags = [
                array_get($purposeTo, 'tag.id'),
                array_get($purposeFor, 'tag.id'),
                array_get(Constants::TAG_SYSTEM, 'TAGS.MISSIONARY')
            ];
            
            $missionary = $this->setSingleContact($person, $tags);

            array_set($purposeFor, 'contact_id', array_get($missionary, 'id', null));
            $purposeFor->update();
        }
    }
    
    public function deleteRelatedCampaigns($purpose)
    {
        $campaigns = $purpose->campaigns;
        
        if ($campaigns->count() > 0) {
            foreach ($campaigns as $campaign) {
                array_set($campaign, 'receive_donations', 0);
                array_set($campaign, 'deleted_at', date('Y-m-d H:i:s'));
                $campaign->update();
            }
        }
    }
}
