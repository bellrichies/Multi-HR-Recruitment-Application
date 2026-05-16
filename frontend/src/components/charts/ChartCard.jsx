import {
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  Legend,
  LinearScale,
  Tooltip,
} from 'chart.js';
import { Bar } from 'react-chartjs-2';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

export function ChartCard({ title, data }) {
  const chartData = data?.labels?.length ? data : { labels: ['No data'], datasets: [{ label: title, data: [0] }] };

  return (
    <div className="rounded-md border border-line bg-white p-4 shadow-sm">
      <h3 className="text-sm font-semibold text-ink">{title}</h3>
      <div className="mt-4 h-64">
        <Bar
          data={chartData}
          options={{
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true, position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
          }}
        />
      </div>
    </div>
  );
}
