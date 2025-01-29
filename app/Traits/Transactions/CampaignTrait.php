<?php

namespace App\Traits\Transactions;

use App\Models\Campaign;
use App\Constants;
/**
 *
 * @author josemiguel
 */
trait CampaignTrait {
    
    /**
     * Store or retrieve Campaign
     * @param Array $jCampaign
     * @param App\Models\Purpose $chart
     * @param App\Models\Contact $contact
     * @return App\Models\Campaign
     */
    public function saveCampaignData($jCampaign = null, $chart = null, $contact = null) {
        if ($jCampaign && $chart) {
            $altId = $this->alternativeIdRetrieve(array_get($jCampaign, 'alt_id', 0), Campaign::class);
            if (!$altId) {
                $campaign = mapModel(new Campaign(), $jCampaign);
                if (is_null(array_get($campaign, 'name'))) {
                    array_set($campaign, 'name', array_get($jCampaign, 'display_name'));
                }
                array_set($campaign, 'purpose_id', array_get($chart, 'id'));
                array_set($campaign, 'contact_id', array_get($contact, 'id'));
                
                if (auth()->user()->tenant->campaigns()->save($campaign)) {
                    $fields = ['alt_id' => array_get($jCampaign, 'alt_id'), 'label' => array_get($campaign, 'name'), 'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY];
                    $this->alternativeIdCreate(array_get($campaign, 'id'), get_class($campaign), $fields);
                    return $campaign;
                }
            }
            else{
                $campaign = array_get($altId, 'getRelationTypeInstanceWithTrashed');
                mapModel($campaign, $jCampaign);
                if (is_null(array_get($campaign, 'name'))) {
                    array_set($campaign, 'name', array_get($jCampaign, 'display_name'));
                }
                array_set($campaign, 'purpose_id', array_get($chart, 'id'));
                array_set($campaign, 'contact_id', array_get($contact, 'id'));
                $campaign->update();
                $campaign->fireEvent('retrieveTag');
            }
            
            return $campaign;
        }
        return null;
    }
    
    public function setSingleCampaign($contact = null, $jCampaign = null, $chart = null) {
        if ($jCampaign && $chart) {
            $altId = $this->alternativeIdRetrieve(array_get($jCampaign, 'alt_id', 0), Campaign::class);
            if (!$altId) {
                $campaign = mapModel(new Campaign(), $jCampaign);
                if (is_null(array_get($campaign, 'name'))) {
                    array_set($campaign, 'name', array_get($jCampaign, 'display_name'));
                }
                array_set($campaign, 'purpose_id', array_get($chart, 'id'));
                array_set($campaign, 'contact_id', array_get($contact, 'id'));
                if (auth()->user()->tenant->campaigns()->save($campaign)) {
                    $fields = ['alt_id' => array_get($jCampaign, 'alt_id'), 'label' => array_get($campaign, 'name'), 'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY];
                    $this->alternativeIdCreate(array_get($campaign, 'id'), get_class($campaign), $fields);
                }
            }
            else{
                $campaign = array_get($altId, 'getRelationTypeInstance');
                mapModel($campaign, $jCampaign);
                if (is_null(array_get($campaign, 'name'))) {
                    array_set($campaign, 'name', array_get($jCampaign, 'display_name'));
                }
                array_set($campaign, 'purpose_id', array_get($chart, 'id'));
                array_set($campaign, 'contact_id', array_get($contact, 'id'));
                $campaign->update();
                $campaign->fireEvent('retrieveTag');
            }
            
            return $campaign;
        }
        return null;
    }
    
}
