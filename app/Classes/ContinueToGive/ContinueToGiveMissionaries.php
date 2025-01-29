<?php

namespace App\Classes\ContinueToGive;

use App\Classes\ContinueToGive\ContinueToGiveIntegration;
use App\Classes\ContinueToGive\Interfaces\ContinueToGiveRunnable;
use App\Traits\Transactions\PurposeTrait;
use App\Traits\Transactions\CampaignTrait;
use App\Traits\Transactions\ContactTrait;
use App\Traits\AlternativeIdTrait;
use App\Classes\MissionPillarsLog;
use App\Models\Purpose;
use App\Constants;

/**
 * Description of ContinueToGiveMissionaries
 *
 * @author josemiguel
 */
class ContinueToGiveMissionaries extends ContinueToGiveIntegration implements ContinueToGiveRunnable {

    use PurposeTrait,
        CampaignTrait,
        ContactTrait,
        AlternativeIdTrait;

    public function __construct($token = null) {
        parent::__construct($token);
    }

    public function call($params = []) {
        $uri = $this->getUri() . 'get/missionaries?token=' . $this->getToken() . '&';
        $uri .= http_build_query($params);

        $response = $this->get('GET', $uri);
        return $response;
    }

    public function run($params = []) {
        $data = $this->call($params);

        if ($data) {
            $pages = array_get($data, 'meta.pagination.total_pages', 0);
            for ($i = 1; $i <= $pages; $i++) {
                array_set($params, 'page', $i);
                $json = $this->call($params);
                $this->store($json);
            }
        }
    }

    public function store($json) {
        if ($json) {
            $dataset = array_get($json, 'data', []);
            foreach ($dataset as $data) {
                $contact = $this->setSingleContact(array_get($data, 'contact'));
                $tags = [];
                if (array_get($data, 'sub_type') === Constants::CHART_OF_ACCOUNT_SUBTYPE_GIVINGPAGES) {
                    $parent = $this->alternativeIdRetrieve(array_get($data, 'highest_for_pageid', 0), Purpose::class);
                    $chart = array_get($parent, 'getRelationTypeInstance');
                    $campaign = $this->setSingleCampaign($contact, $data, $chart);
                    
                    if(!is_null(array_get($chart, 'tagInstance.id'))){
                        array_push($tags, array_get($chart, 'tagInstance.id'));
                    }
                    if(!is_null(array_get($campaign, 'tagInstance.id'))){
                        array_push($tags, array_get($campaign, 'tagInstance.id'));
                    }
                } else {
                    $chart = $this->setSinglePurpose($data);
                    if(!is_null(array_get($chart, 'tagInstance.id'))){
                        array_push($tags, array_get($chart, 'tagInstance.id'));
                    }
                    if (array_get($data, 'highest_for_pageid') != array_get($data, 'alt_id')) {
                        $parent = $this->alternativeIdRetrieve(array_get($data, 'highest_for_pageid', 0), Purpose::class);
                        array_set($chart, 'parent_purposes_id', array_get($parent, 'getRelationTypeInstance.id'));
                        $chart->update();
                    }
                }
                
                if (!is_null($contact) && !empty($tags)) {
                    $contact->tags()->sync($tags, false);
                }
            }
        }
    }

}
