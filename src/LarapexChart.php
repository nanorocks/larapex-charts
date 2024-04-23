<?php

namespace marineusde\LarapexCharts;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class LarapexChart
{
    /*
    |--------------------------------------------------------------------------
    | Chart
    |--------------------------------------------------------------------------
    |
    | This class build the chart by passing setters to the object, it will
    | use the method container and scripts to generate a JSON
    | in blade views, it works also with Vue JS components
    |
    */

    public string $id;
    public string $title = '';
    public string $subtitle = '';
    public string $subtitlePosition = 'left';
    public string $type = 'donut';
    public array $labels = [];
    public string $fontFamily;
    public string $foreColor;
    public string $dataset = '';
    public int $height = 500;
    public int|string $width = '100%';
    public string $colors;
    public string $horizontal;
    public string $xAxis;
    public string $grid;
    public string $markers;
    public bool $stacked = false;
    public bool $showLegend = true;
    public string $stroke = '';
    public string $toolbar;
    public string $zoom;
    public string $dataLabels;
    public string $theme = 'light';
    public string $sparkline;
    public string $chartLetters = 'abcdefghijklmnopqrstuvwxyz';

    public array $additionalOptions = [];

    /*
    |--------------------------------------------------------------------------
    | Constructors
    |--------------------------------------------------------------------------
    */

    public function __construct()
    {
        $this->id = substr(str_shuffle(str_repeat($x = $this->chartLetters, (int) ceil(25 / strlen($x)))), 1, 25);
        $this->horizontal = json_encode(['horizontal' => false]);
        $this->colors = json_encode(config('larapex-charts.colors'));
        $this->setXAxis([]);
        $this->grid = json_encode(['show' => false]);
        $this->markers = json_encode(['show' => false]);
        $this->toolbar = json_encode(['show' => false]);
        $this->zoom = json_encode(['enabled' => true]);
        $this->dataLabels = json_encode(['enabled' => false]);
        $this->sparkline = json_encode(['enabled' => false]);
        $this->fontFamily = config('larapex-charts.font_family');
        $this->foreColor = config('larapex-charts.font_color');
    }

    /*
    |--------------------------------------------------------------------------
    | Setters
    |--------------------------------------------------------------------------
    */

    public function setFontFamily(string $fontFamily): self
    {
        $this->fontFamily = $fontFamily;
        return $this;
    }

    public function setFontColor(string $fontColor): self
    {
        $this->foreColor = $fontColor;
        return $this;
    }

    public function setDataset(array $dataset): self
    {
        $this->dataset = json_encode($dataset);
        return $this;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;
        return $this;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function setColors(array $colors): self
    {
        $this->colors = json_encode($colors);
        return $this;
    }

    public function setHorizontal(bool $horizontal): self
    {
        $this->horizontal = json_encode(['horizontal' => $horizontal]);
        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setSubtitle(string $subtitle, string $position = 'left'): self
    {
        $this->subtitle = $subtitle;
        $this->subtitlePosition = $position;
        return $this;
    }

    public function setLabels(array $labels): self
    {
        $this->labels = $labels;
        return $this;
    }

    public function setXAxis(array $categories): self
    {
        $this->xAxis = json_encode($categories);
        return $this;
    }

    public function setGrid(string $color = '#e5e5e5', float $opacity = 0.1): self
    {
        $this->grid = json_encode([
            'show' => true,
            'row' => [
                'colors' => [$color, 'transparent'],
                'opacity' => $opacity,
            ],
        ]);

        return $this;
    }

    public function setMarkers(array $colors = [], float $width = 4, float $hoverSize = 7): self
    {
        if (empty($colors)) {
            $colors = config('larapex-charts.colors');
        }

        $this->markers = json_encode([
            'size' => $width,
            'colors' => $colors,
            'strokeColors' => '#fff',
            'strokeWidth' => $width / 2,
            'hover' => [
                'size' => $hoverSize,
            ]
        ]);

        return $this;
    }

    public function setStroke(int $width, array $colors = [], string $curve = 'straight'): self
    {
        if (empty($colors)) {
            $colors = config('larapex-charts.colors');
        }

        $this->stroke = json_encode([
            'show'    =>  true,
            'width'   =>  $width,
            'colors'  =>  $colors,
            'curve'   =>  $curve,
        ]);
        return $this;
    }

    public function setToolbar(bool $show, bool $zoom = true): self
    {
        $this->toolbar = json_encode(['show' => $show]);
        $this->zoom = json_encode(['enabled' => $zoom]);
        return $this;
    }

    public function setDataLabels(bool $enabled = true): self
    {
        $this->dataLabels = json_encode(['enabled' => $enabled]);
        return $this;
    }

    public function setTheme(string $theme): self
    {
        $this->theme = $theme;
        return $this;
    }

    public function setSparkline(bool $enabled = true): self
    {
        $this->sparkline = json_encode(['enabled' => $enabled]);
        return $this;
    }

    public function setStacked(bool $stacked = true): self
    {
        $this->stacked = $stacked;
        return $this;
    }

    public function setShowLegend(bool $showLegend = true): self
    {
        $this->showLegend = $showLegend;
        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Methods for Views
    |--------------------------------------------------------------------------
    */

    public function transformLabels(array $array): bool|string
    {
        /* @phpstan-ignore-next-line */
        $stringArray = array_filter($array, function ($string) {
            return "{$string}";
        });
        return json_encode(['"' . implode('","', $stringArray) . '"']);
    }

    public function container(): View
    {
        return view('larapex-charts::chart.container', ['id' => $this->id]);
    }

    public function script(): View
    {
        return view('larapex-charts::chart.script', ['chart' => $this]);
    }

    public static function cdn(): string
    {
        return 'https://cdn.jsdelivr.net/npm/apexcharts';
    }

    /*
    |--------------------------------------------------------------------------
    | JSON Options Builder
    |--------------------------------------------------------------------------
    */

    public function toJson(): JsonResponse
    {
        return response()->json([
            'id' => $this->id,
            'options' => $this->getAdditionalOptions(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Vue Options Builder
    |--------------------------------------------------------------------------
    */

    public function toVue(): array
    {
        return [
            'height' => $this->height,
            'width' => $this->width,
            'type' => $this->type,
            'options' => $this->getAdditionalOptions(),
            'series' => json_decode($this->dataset),
        ];
    }

    public function toJsonEncodedString(): string
    {
        return json_encode($this->getAdditionalOptions());
    }

    public function getAdditionalOptions(): array
    {
        return array_merge_recursive($this->getDefaultOptions(), $this->additionalOptions);
    }

    public function getDefaultOptions(): array
    {
        $options = [
            'chart' => [
                'type' => $this->type,
                'height' => $this->height,
                'width' => $this->width,
                'toolbar' => json_decode($this->toolbar),
                'zoom' => json_decode($this->zoom),
                'fontFamily' => json_decode($this->fontFamily),
                'foreColor' => $this->foreColor,
                'sparkline' => $this->sparkline,
                'stacked' => $this->stacked,
            ],
            'plotOptions' => [
                'bar' => json_decode($this->horizontal),
            ],
            'colors' => json_decode($this->colors),
            'series' => json_decode($this->dataset),
            'dataLabels' => json_decode($this->dataLabels),
            'theme' => [
                'mode' => $this->theme
            ],
            'title' => [
                'text' => $this->title
            ],
            'subtitle' => [
                'text' => $this->subtitle,
                'align' => $this->subtitlePosition,
            ],
            'xaxis' => [
                'categories' => json_decode($this->xAxis),
            ],
            'grid' => json_decode($this->grid),
            'markers' => json_decode($this->markers),
            'legend' => [
                'show' => ($this->showLegend ? 'true' : 'false'),
            ],
        ];

        if ($this->labels !== []) {
            $options['labels'] = $this->labels;
        }

        if ($this->stroke !== '') {
            $options['stroke'] = json_decode($this->stroke);
        }

        return $options;
    }
}
