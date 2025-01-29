<?php

namespace App\Traits;

use App\Models\Purpose;

trait GetsPurposesWithChildren
{
    protected function getPurposesWithChildren($withTrashed = false)
    {
        $query = Purpose::with(['childPurposes' => function ($q) {
            $q->select('parent_purposes_id', 'id', 'name');
            $q->orderBy('name');                // sort child purposes
        }])->select('id', 'name', 'deleted_at')
            ->whereNull('parent_purposes_id')
            ->orderByRaw('tenant_id IS NULL DESC')  // sort by global purposes
            ->orderBy('type', 'desc')
            ->orderBy('name');     // then by name
        if ($withTrashed) $query->withTrashed();
        return $query->get()->prepend((object)['id' => 0, 'name' => 'None']);
    }
    
    protected function getPurposesGrouped($onlyActive = false)
    {
        $organizationPurpose = Purpose::whereNull('parent_purposes_id')->first();
        
        if ($onlyActive) {
            $organizationPurposes = Purpose::where('parent_purposes_id', array_get($organizationPurpose, 'id'))->where('is_active', 1)->orderBy('name')->get();
            $missionaryPurposes = Purpose::with('childPurposes')->where('sub_type', 'missionary')->where('is_active', 1)->whereNull('parent_purposes_id')->where('id', '<>', array_get($organizationPurpose, 'id'))->orderBy('name')->get();
        } else {
            $organizationPurposes = Purpose::where('parent_purposes_id', array_get($organizationPurpose, 'id'))->orderBy('name')->get();
            $missionaryPurposes = Purpose::with('childPurposes')->where('sub_type', 'missionary')->whereNull('parent_purposes_id')->where('id', '<>', array_get($organizationPurpose, 'id'))->orderBy('name')->get();
        }
        
        $purposes = [
            [
                'groupName' => array_get($organizationPurpose, 'name'),
                'children' => [
                    [
                        'id' => array_get($organizationPurpose, 'id'),
                        'name' => array_get($organizationPurpose, 'name')
                    ]
                ]
            ]
        ];
        
        if ($organizationPurposes->count()) {
            $subPurposes = [
                'groupName' => 'All '.array_get($organizationPurpose, 'name').' Purposes',
                'children' => []
            ];
            
            foreach ($organizationPurposes as $purpose) {
                $subPurposes['children'][] = [
                    'id' => array_get($purpose, 'id'),
                    'name' => array_get($purpose, 'name')
                ];
            }
            
            $purposes[] = $subPurposes;
        }
        
        if ($missionaryPurposes->count()) {
            $subPurposes = [
                'groupName' => 'Missionaries',
                'children' => []
            ];
            
            foreach ($missionaryPurposes as $purpose) {
                $subPurposes['children'][] = [
                    'id' => array_get($purpose, 'id'),
                    'name' => array_get($purpose, 'name')
                ];
                
                if (array_get($purpose, 'childPurposes')) {
                    foreach (array_get($purpose, 'childPurposes') as $childPurpose) {
                        $subPurposes['children'][] = [
                            'id' => array_get($childPurpose, 'id'),
                            'name' => array_get($purpose, 'name').' \ '.array_get($childPurpose, 'name')
                        ];
                    }
                }
            }
            
            $purposes[] = $subPurposes;
        }
        
        return $purposes;
    }
}
