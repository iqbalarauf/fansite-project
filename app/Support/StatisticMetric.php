<?php

namespace App\Support;

/**
 * Registry tunggal untuk opsi Statistic Card / blok statistic.
 *
 * Sumber kebenaran untuk: opsi dropdown page builder, dialog rich-text editor,
 * validasi metric, dan flag field (tanggal/setlist/unit song/platform/list).
 */
final class StatisticMetric
{
    public const DEFAULT = 'show_teater_all_setlist';

    /**
     * Metric lama yang tetap diterima (backward compatibility) namun tidak
     * ditampilkan lagi di dropdown.
     *
     * @var array<int, string>
     */
    public const LEGACY = ['show_teater_all'];

    /**
     * @return array<int, array{key: string, label: string, editor: bool, metrics: array<int, array{value: string, label: string}>}>
     */
    public static function groups(): array
    {
        return [
            [
                'key' => 'show',
                'label' => 'Show Teater',
                'editor' => true,
                'metrics' => [
                    ['value' => 'show_teater_setlist', 'label' => 'Count by Setlist'],
                    ['value' => 'show_teater_date_range', 'label' => 'Count all by range date'],
                    ['value' => 'show_teater_all_setlist', 'label' => 'Count all by Setlist'],
                    ['value' => 'show_teater_setlist_count_date_range', 'label' => 'Count Setlist by range date'],
                    ['value' => 'show_teater_active_setlist_count', 'label' => 'Count Active Setlist'],
                    ['value' => 'show_teater_active_setlist_list', 'label' => 'Show List Active Setlist'],
                    ['value' => 'show_teater_all_setlist_list', 'label' => 'Show List All Setlist'],
                    ['value' => 'show_teater_all_setlist_list_count', 'label' => 'Show List All Setlist and Count by Setlist'],
                    ['value' => 'show_teater_show_list_date_range', 'label' => 'Show List Show Teater based on Date Range'],
                ],
            ],
            [
                'key' => 'unit',
                'label' => 'Unit Song',
                'editor' => true,
                'metrics' => [
                    ['value' => 'unit_song_all', 'label' => 'Count All Unit Song'],
                    ['value' => 'unit_song_date_range', 'label' => 'Count all by range date'],
                    ['value' => 'unit_song_setlist', 'label' => 'Count All Unit Song by Setlist'],
                    ['value' => 'unit_song_active_count', 'label' => 'Count Active Unit Song and Setlist'],
                    ['value' => 'unit_song_active_list', 'label' => 'Show List Active Unit Song'],
                    ['value' => 'unit_song_all_list', 'label' => 'Show List Unit Song'],
                    ['value' => 'unit_song_all_list_count', 'label' => 'Show List All Unit Song and Count by Setlist'],
                    ['value' => 'unit_song_show_list_date_range', 'label' => 'Show List Show Teater and Unit Song based on Date Range'],
                ],
            ],
            [
                'key' => 'center',
                'label' => 'Center US',
                'editor' => true,
                'metrics' => [
                    ['value' => 'center_unit_song_all', 'label' => 'Count all'],
                    ['value' => 'center_unit_song_unit_song', 'label' => 'Count by unit song'],
                    ['value' => 'center_unit_song_setlist', 'label' => 'Count by setlist'],
                    ['value' => 'center_unit_song_date_range', 'label' => 'Count by range date'],
                ],
            ],
            [
                'key' => 'global',
                'label' => 'Global Center',
                'editor' => true,
                'metrics' => [
                    ['value' => 'global_center_date_range', 'label' => 'Count by range date'],
                    ['value' => 'global_center_setlist', 'label' => 'Count by setlist'],
                ],
            ],
            [
                'key' => 'live',
                'label' => 'Live Streaming',
                'editor' => false,
                'metrics' => [
                    ['value' => 'live_streaming_time', 'label' => 'Count all by time'],
                    ['value' => 'live_streaming_row', 'label' => 'Count all by row'],
                    ['value' => 'live_streaming_platform', 'label' => 'Count by platform'],
                ],
            ],
        ];
    }

    /**
     * Grup untuk dialog rich-text editor.
     *
     * @return array<int, array{key: string, label: string, editor: bool, metrics: array<int, array{value: string, label: string}>}>
     */
    public static function editorGroups(): array
    {
        return array_values(array_filter(self::groups(), fn (array $group): bool => $group['editor']));
    }

    /**
     * Flag field yang dipakai untuk mengetik metric.
     *
     * @return array{value: string, label: string, group: string, date: bool, setlist: bool, unit_song: bool, platform: bool, list: bool}
     */
    public static function config(string $value): array
    {
        foreach (self::groups() as $group) {
            foreach ($group['metrics'] as $metric) {
                if ($metric['value'] === $value) {
                    return [
                        'value' => $value,
                        'label' => $metric['label'],
                        'group' => $group['key'],
                        'date' => in_array($value, self::dateMetrics(), true),
                        'setlist' => in_array($value, self::setlistMetrics(), true),
                        'unit_song' => $value === 'center_unit_song_unit_song',
                        'platform' => $value === 'live_streaming_platform',
                        'list' => in_array($value, self::listMetrics(), true),
                    ];
                }
            }
        }

        return self::config(self::DEFAULT);
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        $values = [];

        foreach (self::groups() as $group) {
            foreach ($group['metrics'] as $metric) {
                $values[] = $metric['value'];
            }
        }

        return array_values(array_unique(array_merge($values, self::LEGACY)));
    }

    /**
     * @return array<int, string>
     */
    public static function dateMetrics(): array
    {
        return [
            'show_teater_date_range',
            'show_teater_setlist_count_date_range',
            'show_teater_show_list_date_range',
            'unit_song_date_range',
            'unit_song_show_list_date_range',
            'center_unit_song_date_range',
            'global_center_date_range',
            'live_streaming_time',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function setlistMetrics(): array
    {
        return [
            'show_teater_setlist',
            'unit_song_setlist',
            'center_unit_song_setlist',
            'global_center_setlist',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function listMetrics(): array
    {
        return [
            'show_teater_active_setlist_list',
            'show_teater_all_setlist_list',
            'show_teater_all_setlist_list_count',
            'show_teater_show_list_date_range',
            'unit_song_active_list',
            'unit_song_all_list',
            'unit_song_all_list_count',
            'unit_song_show_list_date_range',
        ];
    }

    /**
     * Opsi untuk select page builder (flat, dengan prefiks grup).
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::groups() as $group) {
            foreach ($group['metrics'] as $metric) {
                $options[] = [
                    'value' => $metric['value'],
                    'label' => $group['label'].': '.$metric['label'],
                ];
            }
        }

        return $options;
    }
}
