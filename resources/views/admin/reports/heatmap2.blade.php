@extends('layouts.admin')

@section('title')
    Heatmap v2
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        Heatmap v2
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
        <canvas id="ipv4Heatmap" width="800" height="600" style="background: white; border: 1px solid #ccc;"></canvas>
        <div id="legend" class="mt-3"></div>
    </div>
</div>
@endsection

@section('scripts')
@vite(['resources/charts/chart-matrix.js'])

<script @nonce>
  // --- IP de test dans 10.0.0.0/16 ---
  const ipList = [
    '10.0.0.1', '10.0.0.2',
    '10.1.1.1',
    '10.2.0.1', '10.2.0.2', '10.2.0.3',
    '10.10.10.10', '10.10.10.11', '10.10.10.12', '10.10.10.13',
    '10.20.20.1', '10.20.21.1',
    '10.20.30.1', '10.20.61.1',
    '10.10.90.1', '10.21.81.1',
    '10.40.20.1', '10.23.21.1',
    '10.48.20.1', '10.123.21.1',
    '10.49.20.1', '10.123.21.1',
    '10.49.120.1', '10.123.21.1',
    '10.249.20.1', '10.23.28.1',
    '10.149.20.1', '10.223.26.1',
    '10.100.100.100'
  ];

document.addEventListener('DOMContentLoaded', function () {



  const canvas = document.getElementById('ipv4Heatmap');
  const ctx = canvas.getContext('2d');

  // --- Fonctions Hilbert (ordre 8) ---
  function rot(n, x, y, rx, ry) {
    if (ry === 0) {
      if (rx === 1) {
        x = n - 1 - x;
        y = n - 1 - y;
      }
      return [y, x];
    }
    return [x, y];
  }

  function hilbertIndexToXY(index, order) {
    let x = 0, y = 0;
    for (let s = 1; s < (1 << order); s <<= 1) {
      const rx = 1 & (index >> 1);
      const ry = 1 & (index ^ rx);
      const [nx, ny] = rot(s, x, y, rx, ry);
      x = nx + s * rx;
      y = ny + s * ry;
      index >>= 2;
    }
    return { x, y };
  }

  function ipToIndex(ip) {
    const parts = ip.split('.').map(Number);
    return (parts[1] << 8) + parts[2]; // 10.X.Y.Z
  }


// Regrouper les IPs par bloc /24
const blocks = {}; // index → { v, ipList }

ipList.forEach(ip => {
  const parts = ip.split('.').map(Number);
  const index = (parts[1] << 8) + parts[2]; // pour Hilbert
  const key = `${parts[1]}.${parts[2]}`;   // bloc /24

  if (!blocks[index]) {
    blocks[index] = { v: 0, ipList: [], key };
  }

  blocks[index].v += 1;
  blocks[index].ipList.push(ip);
});

// --- Calcul du chemin de la courbe de Hilbert ---
const orderHeatmap = 8;
const orderCurve = 5;
const scale = Math.pow(2, orderHeatmap - orderCurve);

const fullHilbertPath = [];
for (let i = 0; i < Math.pow(2, orderCurve * 2); i++) {
  const { x, y } = hilbertIndexToXY(i, orderCurve);
  fullHilbertPath.push({
    x: x * scale + scale / 2,
    y: y * scale + scale / 2
  });
}

// Position des points
const points = Object.entries(blocks).map(([idx, { v, ipList, key }]) => {
  const { x, y } = hilbertIndexToXY(parseInt(idx), orderHeatmap);
  return { x, y, v, ipList, block: `10.${key}.0/24` };
});

  // Regrouper les occurrences
  const counts = {};
  ipList.forEach(ip => {
    const idx = ipToIndex(ip);
    counts[idx] = (counts[idx] || 0) + 1;
  });


// Créer la légende dynamiquement
const legendDiv = document.getElementById('legend');
const maxV = Math.max(...points.map(p => p.v));

legendDiv.innerHTML = `
  <label>Densité :</label>
  <div style="display: flex; align-items: center; gap: 8px; width:800px">
    <span style="font-size: 0.8rem;">0</span>
    <div style="flex: 1; height: 20px; background: linear-gradient(to right, rgba(0,123,255,0.1), rgba(0,123,255,1)); border: 1px solid #ccc;"></div>
    <span style="font-size: 0.8rem;">${maxV}</span>
  </div>
`;

// Chartjs
  const chart = new Chart(ctx, {
    type: 'matrix',
    data: {
      datasets: [
        {
          label: 'Courbe de Hilbert',
          data: fullHilbertPath, // [],
          type: 'line',
          borderColor: 'rgba(0,0,0,0.2)',
          borderWidth: 1,
          pointRadius: 0,
          tension: 0, // pour garder les angles bruts
          order: 0
        },
        {
          label: 'IPv4 Scatter',
          type: 'scatter',
          data: points,
          backgroundColor(ctx) {
            const value = ctx.raw?.v ?? 0;
            const alpha = Math.min(1, value / maxV);
            return `rgba(0, 123, 255, ${alpha})`;
          },
          pointRadius(ctx) {
            const v = ctx.raw?.v ?? 1;
            return Math.min(20, Math.max(3, v * 2)); // taille dynamique entre 3 et 20
          },
          borderWidth: 0,
          order: 1
        }

    ]
    },
    options: {
      responsive: false,
      animation: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            title: () => '',
            label(ctx) {
              const { block, v, ipList } = ctx.raw;

              if (!ipList || !block || typeof v !== 'number') {
                return ''; // ou return [`Index : ${ctx.raw.x}, ${ctx.raw.y}`] // pour une info générique
              }

              const shownIps = ipList.length > 5
                ? ipList.slice(0, 5).join(', ') + ', …'
                : ipList.join(', ');

              return [
                `Bloc : ${block}`,
                `Nombre d'adresses : ${v}`,
                `IPs : ${shownIps}`
              ];
            }
          }
        },
      },
      layout: { padding: 0 },



scales: {
  x: {
    type: 'linear',
    min: 0,
    max: 256,
    display: true,
    grid: {
      display: true,
      color: 'rgba(0, 0, 0, 0.1)',
      lineWidth: 0.5,
      drawTicks: false,
    },
    ticks: {
      stepSize: 32, // lignes tous les 32 blocs (optionnel)
      display: true,
      callback: val => val % 64 === 0 ? val : '',
    }
  },
  y: {
    type: 'linear',
    min: 0,
    max: 256,
    reverse: true,
    display: true,
    grid: {
      display: true,
      color: 'rgba(0, 0, 0, 0.1)',
      lineWidth: 0.5,
      drawTicks: false,
    },
    ticks: {
      stepSize: 32,
      display: true,
      callback: val => val % 64 === 0 ? val : '',
    }
  }
}



    }
  });



});

</script>
@endsection
