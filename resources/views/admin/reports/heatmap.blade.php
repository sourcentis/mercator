@extends('layouts.admin')

@section('title')
    Heatmap
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        Heatmap
    </div>
    <div class="card-body">
        <div class="col-3">
            <div class="form-group">
                <label for="rangeSelect" class="form-label">Filtrer par plage :</label>
                <select id="rangeSelect" name="rangeSelect" class="form-select select2">
                    <option value="">Toutes les plages privées</option>
                    <option value="10">10.0.0.0/8</option>
                    <option value="172">172.16.0.0/12</option>
                    <option value="192">192.168.0.0/16</option>
                </select>
            </div>
        </div>
        <canvas id="ipv4Heatmap" width="900" height="400"></canvas>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix@1.3.0/dist/chartjs-chart-matrix.min.js"></script>

<script @nonce>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('ipv4Heatmap');
    const ctx = canvas.getContext('2d');
    const select = document.getElementById('rangeSelect');

    let chart;

    function loadHeatmap(range = '') {
        const url = range ? `/admin/report/heatmap/values?range=${range}` : '/admin/report/heatmap/values';

        console.log(url);

        fetch(url)
            .then(res => res.json())
            .then(data => {

            data = [
              { x: 0, y: 0, v: 5, range: '10' },
              { x: 1, y: 0, v: 15, range: '10' },
              { x: 2, y: 0, v: 30, range: '10' },
              { x: 0, y: 1, v: 10, range: '10' },
              { x: 0, y: 1, v: 40, range: '10' },
              { x: 1, y: 1, v: 25, range: '10' },
              { x: 2, y: 1, v: 45, range: '10' },
              { x: 3, y: 2, v: 50, range: '10' },
              { x: 4, y: 0, v: 10, range: '10' },
              { x: 4, y: 1, v: 10, range: '10' },
              { x: 4, y: 2, v: 10, range: '10' },
              { x: 5, y: 2, v: 20, range: '10' },
              { x: 5, y: 4, v: 20, range: '10' },
              { x: 1, y: 7, v: 20, range: '10' },
              { x: 8, y: 8, v: 0, range: '10' },
            ];

                console.log(data);
                if (!data.length) {
                    console.warn("Aucune donnée pour la plage sélectionnée.");
                    return;
                }

    const adjustedData = data.map(d => ({
      ...d,
      x: d.x - 0.5,
      y: d.y - 0.5
    }));

const minX = Math.min(...data.map(d => d.x)) - 0.5;
const maxX = Math.max(...data.map(d => d.x)) + 0.5;
const minY = Math.min(...data.map(d => d.y)) - 0.5;
const maxY = Math.max(...data.map(d => d.y)) + 0.5;

const cellWidth = canvas.width / maxX * 0.8;
const cellHeight = canvas.height / maxY * 0.85;


                if (chart) {
                    chart.destroy();
                }

                chart = new Chart(ctx, {
                    type: 'matrix',
                    data: {
                        datasets: [{
                            label: 'IPv4 Heatmap',
                            data: data,
                            width: () => cellWidth,
                            height: () => cellHeight,
                            backgroundColor(ctx) {
                                const value = ctx.raw?.v ?? 0;
                                const plage = ctx.raw?.range ?? 'other';
                                const alpha = Math.min(1, value / 50);

                                const baseColors = {
                                    '10': [0, 123, 255],     // bleu
                                    '172': [40, 167, 69],    // vert
                                    '192': [255, 193, 7],    // orange
                                    'other': [108, 117, 125] // gris
                                };

                                const [r, g, b] = baseColors[plage] ?? baseColors['other'];
                                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
                            },
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: false,
                        animation: {
                          duration: 800,
                          easing: 'easeOutQuart'
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    title: () => '',
                                    label(ctx) {
                                        const { x, y, v, range } = ctx.raw;
                                        return `${range}.${y}.${x}.0/24 : ${v} IP`;
                                    }
                                }
                            }
                        },
                        layout: {
                            padding: 0
                        },



                        scales: {
                          x: {
                            type: 'linear',
                            position: 'top',
                            display: true,
                            ticks: {
                              stepSize: 1,
                              callback: val => `10.${Math.round(val + 0.5)}.0.0/16`
                            },
                            title: {
                              display: true,
                              text: '3e octet (/24)'
                            },
                            min: minX,
                            max: maxX
                          },
                          y: {
                            type: 'linear',
                            display: true,
                            reverse: true,
                            ticks: {
                              stepSize: 1,
                              callback: val => `${Math.round(val + 0.5)}.0/24`
                            },
                            title: {
                              display: true,
                              text: '2e octet (/16 logique)'
                            },
                            min: minY,
                            max: maxY
                          }
                        }


                    }
                });
            });
    }

    // Charger initialement tout
    loadHeatmap();

    // Écouteur du menu déroulant
    select.addEventListener('change', () => {
        const selected = select.value;
        loadHeatmap(selected);
    });
});
</script>
@parent
@endsection
