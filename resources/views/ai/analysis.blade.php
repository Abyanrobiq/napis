@extends('layouts.app')

@section('title', 'AI Spending Analysis')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">🤖 AI Spending Pattern Analysis</h1>
        <p class="text-gray-500 text-sm mt-1">Smart insights about your spending behavior</p>
    </div>

    <!-- Trend Analysis -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="text-xl font-bold mb-4">Spending Trend</h2>
        <div class="flex items-center gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-4xl">{{ $trend === 'increasing' ? '📈' : '📉' }}</span>
                    <div>
                        <p class="text-sm text-gray-600">Your spending is</p>
                        <p class="text-2xl font-bold {{ $trend === 'increasing' ? 'text-red-600' : 'text-green-600' }}">
                            {{ ucfirst($trend) }}
                        </p>
                    </div>
                </div>
                <p class="text-gray-600">
                    {{ abs($trendPercentage) > 0 ? number_format(abs($trendPercentage), 1) . '% ' . ($trend === 'increasing' ? 'higher' : 'lower') : 'Same' }} 
                    compared to last month
                </p>
            </div>
            <div class="flex gap-8">
                <div>
                    <p class="text-sm text-gray-600">This Month</p>
                    <p class="text-xl font-bold">Rp {{ number_format($thisMonthExpense, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Last Month</p>
                    <p class="text-xl font-bold">Rp {{ number_format($lastMonthExpense, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Analysis -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="text-xl font-bold mb-4">Spending by Category (Last 30 Days)</h2>
        <div class="space-y-4">
            @foreach($categoryAnalysis as $analysis)
            <div class="border-b pb-4 last:border-b-0">
                <div class="flex justify-between items-center mb-2">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ $analysis->category->icon ?? '🏷️' }}</span>
                        <div>
                            <h3 class="font-bold">{{ $analysis->category->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $analysis->count }} transactions</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold text-red-600">Rp {{ number_format($analysis->total, 0, ',', '.') }}</p>
                        <p class="text-sm text-gray-500">Avg: Rp {{ number_format(($analysis->total / max(1, $analysis->count)), 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Anomaly Detection -->
    @if(count($anomalies) > 0)
    <div class="bg-orange-50 border border-orange-200 rounded-2xl p-6">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-2xl">⚠️</span>
            <h2 class="text-xl font-bold text-orange-800">Unusual Transactions Detected</h2>
        </div>
        <div class="space-y-3">
            @foreach($anomalies as $anomaly)
            <div class="bg-white rounded-lg p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-semibold">{{ $anomaly['transaction']->description }}</p>
                        <p class="text-sm text-gray-600">
                            {{ $anomaly['transaction']->category->name ?? 'Unknown' }} • 
                            {{ \Carbon\Carbon::parse($anomaly['transaction']->transaction_date)->format('d M Y') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-red-600">Rp {{ number_format($anomaly['transaction']->amount, 0, ',', '.') }}</p>
                        <p class="text-xs text-orange-600">+Rp {{ number_format($anomaly['difference'], 0, ',', '.') }} above average</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- AI Insights -->
    <div class="bg-gradient-to-br from-purple-50 to-blue-50 rounded-2xl p-6 border border-purple-200">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-2xl">💡</span>
            <h2 class="text-xl font-bold text-purple-800">AI Insights & Recommendations</h2>
        </div>
        <div class="space-y-3">
            @if($trend === 'increasing' && $trendPercentage > 10)
            <div class="bg-white rounded-lg p-4">
                <p class="font-semibold text-purple-800">⚠️ High Spending Alert</p>
                <p class="text-sm text-gray-700 mt-1">Your spending increased by {{ number_format($trendPercentage, 1) }}%. Consider reviewing your budget and cutting unnecessary expenses.</p>
            </div>
            @endif

            @if(count($anomalies) > 0)
            <div class="bg-white rounded-lg p-4">
                <p class="font-semibold text-purple-800">🔍 Unusual Activity</p>
                <p class="text-sm text-gray-700 mt-1">We detected {{ count($anomalies) }} unusual transaction(s). Make sure these are legitimate expenses.</p>
            </div>
            @endif

            <div class="bg-white rounded-lg p-4">
                <p class="font-semibold text-purple-800">📊 Pattern Recognition</p>
                <p class="text-sm text-gray-700 mt-1">Based on your spending pattern, we recommend setting up budgets for your top spending categories.</p>
            </div>

            <div class="bg-white rounded-lg p-4">
                <p class="font-semibold text-purple-800">🎯 Next Steps</p>
                <p class="text-sm text-gray-700 mt-1">
                    <a href="{{ route('ai.budget-recommendation') }}" class="text-blue-600 hover:underline">View AI Budget Recommendations</a> • 
                    <a href="{{ route('ai.reminders') }}" class="text-blue-600 hover:underline">Check Smart Reminders</a>
                </p>
            </div>
        </div>
    </div>

    <!-- REKOMENDASI PENGELUARAN BULAN DEPAN (line + bubble + per-kategori) -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="text-xl font-bold mb-2">🔮 Rekomendasi Pengeluaran Bulan Berikutnya</h2>
        <p class="text-gray-600 mb-3">
            Berdasarkan data 6 bulan terakhir. <strong>Catatan:</strong> Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum..
        </p>

        @php
            if ($nextMonthPrediction > $thisMonthExpense && $thisMonthExpense > 0) {
                $recommendedTarget = $thisMonthExpense;
                $recommendationReason = "Prediksi lebih tinggi dari pengeluaran bulan ini. Direkomendasikan menjaga total pengeluaran di level bulan ini.";
            } else {
                $recommendedTarget = $nextMonthPrediction > 0 ? round($nextMonthPrediction * 0.95) : 0;
                $recommendationReason = "Prediksi tidak melebihi pengeluaran saat ini. Direkomendasikan sedikit buffer (-5%).";
            }
        @endphp

        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-sm text-gray-500">Prediksi (regresi linear)</p>
                <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($nextMonthPrediction,0,',','.') }}</p>
            </div>

            <div class="text-right">
                <p class="text-sm text-gray-500">Rekomendasi Target Bulan Depan</p>
                <p class="text-2xl font-bold text-green-600">Rp {{ number_format($recommendedTarget,0,',','.') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $recommendationReason }}</p>
            </div>
        </div>

        <div style="height:420px;">
            <canvas id="predictionChart" style="max-height:420px;"></canvas>
        </div>

        <!-- Category-cut suggestions / recommended per-category budgets -->
        <div class="mt-6">
            <h3 class="font-bold mb-2">Rekomendasi Anggaran Per Kategori</h3>

            @if($nextMonthPrediction > $thisMonthExpense && count($categorySuggestions) > 0)
                <p class="text-sm text-gray-600 mb-3">Prediksi lebih tinggi Rp {{ number_format($nextMonthPrediction - $thisMonthExpense,0,',','.') }} dari bulan ini. Untuk mencapai target, pertimbangkan pemotongan proporsional berikut:</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($categorySuggestions as $cs)
                    <div class="bg-white p-3 border rounded-lg">
                        <div class="flex justify-between items-start gap-3">
                            <div>
                                <p class="font-semibold">{{ $cs['category_name'] }}</p>
                                <p class="text-xs text-gray-500">Total bulan lalu: Rp {{ number_format($cs['last_month_total'],0,',','.') }}</p>
                                <p class="text-xs text-gray-500">Kontribusi: {{ $cs['share_of_top'] }}% dari kelompok top</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-red-600">Potong Rp {{ number_format($cs['cut_amount'],0,',','.') }}</p>
                                <p class="text-xs text-gray-500">~{{ $cs['percent_reduction'] }}% dari kategori ini</p>
                                <p class="text-sm font-semibold mt-2">Rekomendasi: Rp {{ number_format(max(0, $cs['last_month_total'] - $cs['cut_amount']),0,',','.') }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-600 mb-3">Tidak perlu pemotongan besar. Perhatikan kategori berikut sebagai fokus penghematan:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($categorySuggestions as $cs)
                    <div class="bg-white p-3 border rounded-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold">{{ $cs['category_name'] }}</p>
                                <p class="text-xs text-gray-500">Total bulan lalu: Rp {{ number_format($cs['last_month_total'],0,',','.') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-700">Share {{ $cs['share_of_top'] }}%</p>
                                <p class="text-xs text-gray-500">Monitor & atur budget jika naik</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // data dari controller
    var months = @json($months);        // [1,2,3,4,5,6]
    var values = @json($values);        // pengeluaran per bulan
    var prediction = {{ $nextMonthPrediction }}; // angka prediksi

    // prepare arrays for plotting
    var labels = months.concat([7]); // 1..7
    var lineData = values.concat([prediction]); // untuk garis
    var bubbleData = lineData.map(function(v, idx){
        return { x: idx+1, y: v, r: idx === lineData.length -1 ? 14 : 9 };
    });

    var ctx = document.getElementById('predictionChart').getContext('2d');

    new Chart(ctx, {
        data: {
            labels: labels,
            datasets: [
                {
                    type: 'line',
                    label: 'Garis Tren',
                    data: lineData,
                    borderColor: 'rgba(54,162,235,0.95)',
                    backgroundColor: 'rgba(54,162,235,0.12)',
                    tension: 0.25,
                    fill: true,
                    pointRadius: 0
                },
                {
                    type: 'bubble',
                    label: 'Actual + Prediksi',
                    data: bubbleData,
                    backgroundColor: bubbleData.map((d,i) => i === bubbleData.length -1 ? 'rgba(30,144,255,0.95)' : 'rgba(255,159,64,0.75)'),
                    borderColor: bubbleData.map((d,i) => i === bubbleData.length -1 ? 'rgba(0,102,204,1)' : 'rgba(255,99,32,1)'),
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    title: { display: true, text: 'Bulan' },
                    ticks: { stepSize: 1 }
                },
                y: {
                    title: { display: true, text: 'Pengeluaran (Rp)' },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            var idx = context[0].dataIndex + 1;
                            if (idx === labels.length) return 'Prediksi — Bulan ke-' + idx;
                            return 'Bulan ke-' + idx;
                        },
                        label: function(context) {
                            var y = context.raw.y !== undefined ? context.raw.y : context.raw;
                            return 'Rp ' + y.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        },
                        afterLabel: function(context) {
                            var idx = context.dataIndex;
                            if (idx === labels.length - 1) return ' (hasil regresi linear)';
                            return '';
                        }
                    }
                },
                legend: { display: false }
            }
        }
    });
</script>

@endsection
