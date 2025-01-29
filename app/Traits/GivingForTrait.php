<?php

namespace App\Traits;

/**
 *
 * @author josemiguel
 */
trait GivingForTrait {
    public function givingFor() {
        $for = [];
        if ($this->campaign_id > 1) {
            array_push($for, array_get($this, 'campaign.name'));
        }
        
        $chart = array_get($this, 'purpose');
        while ($chart) {
            array_push($for, array_get($chart, 'name'));
            $chart = array_get($chart, 'getParent');
        }
        
        return implode(' / ', array_reverse($for));
    }
}
