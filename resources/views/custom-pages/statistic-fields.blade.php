@php
	$metric = data_get($this, "{$path}.data.metric", 'show_teater_all');
	$dateMetrics = ['show_teater_date_range', 'unit_song_date_range', 'center_unit_song_date_range', 'global_center_date_range', 'live_streaming_time'];
	$setlistMetrics = ['show_teater_setlist', 'unit_song_setlist', 'center_unit_song_setlist', 'global_center_setlist'];
@endphp

<flux:input wire:model.live="{{ $path }}.data.label" :label="__('Label')" />
<flux:select wire:model.live="{{ $path }}.data.metric" :label="__('Data source')">
	<flux:select.option value="show_teater_all">{{ __('Show Teater: Count all') }}</flux:select.option>
	<flux:select.option value="show_teater_date_range">{{ __('Show Teater: Count by range date') }}</flux:select.option>
	<flux:select.option value="show_teater_setlist">{{ __('Show Teater: Count by setlist') }}</flux:select.option>
	<flux:select.option value="unit_song_all">{{ __('Unit Song: Count all') }}</flux:select.option>
	<flux:select.option value="unit_song_date_range">{{ __('Unit Song: Count by range date') }}</flux:select.option>
	<flux:select.option value="unit_song_setlist">{{ __('Unit Song: Count by setlist') }}</flux:select.option>
	<flux:select.option value="center_unit_song_all">{{ __('Center Unit Song: Count all') }}</flux:select.option>
	<flux:select.option value="center_unit_song_unit_song">{{ __('Center Unit Song: Count by unit song') }}</flux:select.option>
	<flux:select.option value="center_unit_song_setlist">{{ __('Center Unit Song: Count by setlist') }}</flux:select.option>
	<flux:select.option value="center_unit_song_date_range">{{ __('Center Unit Song: Count by range date') }}</flux:select.option>
	<flux:select.option value="global_center_date_range">{{ __('Global Center: Count by range date') }}</flux:select.option>
	<flux:select.option value="global_center_setlist">{{ __('Global Center: Count by setlist') }}</flux:select.option>
	<flux:select.option value="live_streaming_time">{{ __('Live Streaming: Count all by time') }}</flux:select.option>
	<flux:select.option value="live_streaming_row">{{ __('Live Streaming: Count all by row') }}</flux:select.option>
	<flux:select.option value="live_streaming_platform">{{ __('Live Streaming: Count by platform') }}</flux:select.option>
</flux:select>

@if (in_array($metric, $dateMetrics, true))
	<div class="grid gap-4 sm:grid-cols-2">
		<flux:input wire:model.live="{{ $path }}.data.date_from" :label="__('Start date')" type="date" />
		<flux:input wire:model.live="{{ $path }}.data.date_to" :label="__('End date')" type="date" />
	</div>
@endif

@if (in_array($metric, $setlistMetrics, true))
	<flux:select wire:model.live="{{ $path }}.data.setlist" :label="__('Setlist')">
		<flux:select.option value="">{{ __('Select setlist') }}</flux:select.option>
		@foreach ($setlistOptions as $setlist)
			<flux:select.option value="{{ $setlist }}">{{ $setlist }}</flux:select.option>
		@endforeach
	</flux:select>
@endif

@if ($metric === 'center_unit_song_unit_song')
	<flux:select wire:model.live="{{ $path }}.data.unit_song" :label="__('Unit song')">
		<flux:select.option value="">{{ __('Select unit song') }}</flux:select.option>
		@foreach ($unitSongOptions as $unitSong)
			<flux:select.option value="{{ $unitSong }}">{{ $unitSong }}</flux:select.option>
		@endforeach
	</flux:select>
@endif

@if ($metric === 'live_streaming_platform')
	<flux:select wire:model.live="{{ $path }}.data.platform" :label="__('Platform')">
		<flux:select.option value="IDN App">IDN App</flux:select.option>
		<flux:select.option value="Showroom">Showroom</flux:select.option>
	</flux:select>
@endif
