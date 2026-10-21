@php
	use App\Support\StatisticMetric;

	$metric = data_get($this, "{$path}.data.metric", StatisticMetric::DEFAULT);
	$metricConfig = StatisticMetric::config($metric);
@endphp

<flux:input wire:model.live="{{ $path }}.data.label" :label="__('Label')" />
<flux:select wire:model.live="{{ $path }}.data.metric" :label="__('Data source')">
	@foreach (StatisticMetric::options() as $option)
		<flux:select.option value="{{ $option['value'] }}">{{ __($option['label']) }}</flux:select.option>
	@endforeach
</flux:select>

@if ($metricConfig['date'])
	<div class="grid gap-4 sm:grid-cols-2">
		<flux:input wire:model.live="{{ $path }}.data.date_from" :label="__('Start date')" type="date" />
		<flux:input wire:model.live="{{ $path }}.data.date_to" :label="__('End date')" type="date" />
	</div>
@endif

@if ($metricConfig['setlist'])
	<flux:select wire:model.live="{{ $path }}.data.setlist" :label="__('Setlist')">
		<flux:select.option value="">{{ __('Select setlist') }}</flux:select.option>
		@foreach ($setlistOptions as $setlist)
			<flux:select.option value="{{ $setlist }}">{{ $setlist }}</flux:select.option>
		@endforeach
	</flux:select>
@endif

@if ($metricConfig['unit_song'])
	<flux:select wire:model.live="{{ $path }}.data.unit_song" :label="__('Unit song')">
		<flux:select.option value="">{{ __('Select unit song') }}</flux:select.option>
		@foreach ($unitSongOptions as $unitSong)
			<flux:select.option value="{{ $unitSong }}">{{ $unitSong }}</flux:select.option>
		@endforeach
	</flux:select>
@endif

@if ($metricConfig['platform'])
	<flux:select wire:model.live="{{ $path }}.data.platform" :label="__('Platform')">
		<flux:select.option value="IDN App">IDN App</flux:select.option>
		<flux:select.option value="Showroom">Showroom</flux:select.option>
	</flux:select>
@endif
