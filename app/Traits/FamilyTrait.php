<?php

namespace App\Traits;

trait FamilyTrait 
{
    public function getFamilyRelationships() 
    {
        return [
            'Husband' => __('Husband'),
            'Wife' => __('Wife'),
            'Father' => __('Father'),
            'Mother' => __('Mother'),
            'Son' => __('Son'),
            'Daughter' => __('Daughter'),
            'Aunt' => __('Aunt'),
            'Brother' => __('Brother'),
            'Cousin' => __('Cousin'),
            'Granddaughter' => __('Granddaughter'),
            'Grandfather' => __('Grandfather'),
            'Grandmother' => __('Grandmother'),
            'Grandson' => __('Grandson'),
            'Nephew' => __('Nephew'),
            'Niece' => __('Niece'),
            'Sister' => __('Sister'),
            'Uncle' => __('Uncle'),
            'Employer' => __('Employer'),
            'Employee' => __('Employee'),
            'Pastor' => __('Pastor'),
            'Other' => __('Other')
        ];
    }
    
    public function getOrganizationRelationships()
    {
        return [
            'Employer' => __('Employer'),
            'Employee' => __('Employee'),
            'Pastor' => __('Pastor'),
            'Other' => __('Other')
        ];
    }
    
    public function getFamilyPositions()
    {
        return [
            ' ' => ' ',
            'Primary Contact' => __('Primary Contact'),
            'Spouse' => __('Spouse'), 
            'Child' => __('Child'), 
            'Other' => __('Other')
        ];
    }
}
