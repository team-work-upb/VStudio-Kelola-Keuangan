<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Models\Transaction;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

/**
 * Class WidgetIncomeChart
 * 
 * Widget class for displaying income trends in a chart.
 */
class WidgetIncomeChart extends ChartWidget
{
    use InteractsWithPageFilters;

    /**
     * The heading of the widget.
     *
     * @var string|null
     */
    protected static ?string $heading = 'Penghasilan';

    /**
     * The color of the widget.
     *
     * @var string
     */
    protected static string $color = 'success';

    /**
     * Get the data for the chart.
     *
     * @return array
     */
    protected function getData(): array
    {
        // Parse the start date from filters or set to the start of the current year if not provided
        $startDate = !is_null($this->filters['startDate'] ?? null) ?
            Carbon::parse($this->filters['startDate']) :
            now()->startOfYear();

        // Parse the end date from filters or set to the current date if not provided
        $endDate = !is_null($this->filters['endDate'] ?? null) ?
            Carbon::parse($this->filters['endDate']) :
            now();

        // Query the income data and aggregate it per day
        $data = Trend::query(Transaction::income()->newQuery())
            ->between(
                start: $startDate,
                end: $endDate,
            )
            ->perDay()
            ->sum('amount');

        // Ensure the data is sorted by date
        $data = $data->sortBy('date');

        // Set chart color to green
        $backgroundColor = 'rgba(75, 192, 192, 0.2)'; // Green
        $borderColor = 'rgba(75, 192, 192, 1)'; // Green

        // Return the chart data
        return [
            'datasets' => [
                [
                    'label' => 'Penghasilan Per Hari',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => $borderColor,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('Y-m-d')),
        ];
    }

    /**
     * Get the type of the chart.
     *
     * @return string
     */
    protected function getType(): string
    {
        return 'bar';
    }
}