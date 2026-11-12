// Chart.js + plugin matrix pour les heatmaps (admin/reports/heatmap*.blade.php).
// Embarqués dans le bundle plutôt que chargés depuis un CDN : la CSP (script-src 'self' + nonce)
// n'autorise aucune origine externe. Les scripts inline des vues utilisent window.Chart après
// DOMContentLoaded — un module Vite s'exécute avant cet événement.
import { Chart, registerables } from 'chart.js';
import { MatrixController, MatrixElement } from 'chartjs-chart-matrix';

Chart.register(...registerables, MatrixController, MatrixElement);

window.Chart = Chart;
