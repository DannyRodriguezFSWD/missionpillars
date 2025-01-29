@php
    $sortedcampaigns = $campaigns->sortBy('name');
    $css = '';
@endphp
@foreach ($sortedcampaigns as $campaign)
    <option value="{{$campaign->id}}" class="campaign_option" style="{{ $css }}">{{ $campaign->name }}</option>
@endforeach
