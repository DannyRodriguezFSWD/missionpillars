@include('_partials.select-search', ['name' => 'timezone', 'label' => 'Select Timezone', 'options' => $timezones, 'data' => array_get($split, 'template.timezone', session('timezone'))])
