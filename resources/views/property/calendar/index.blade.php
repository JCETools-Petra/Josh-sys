<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Kalender Reservasi') }}
            </h2>
            <a href="{{ route('property.reservations.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                + Reservasi Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-4">Grafik Okupansi (30 Hari Terakhir)</h3>
                    <div class="h-80 relative"><canvas id="occupancyChart"></canvas></div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Filter Controls -->
                    <div class="mb-6 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-gray-700 dark:to-gray-600 rounded-lg border border-indigo-200 dark:border-gray-600">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                Filter Tampilan
                            </h4>
                            <div class="flex gap-2">
                                <button onclick="selectAllFilters()" class="text-xs px-3 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-500 rounded hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                    ✓ Pilih Semua
                                </button>
                                <button onclick="clearAllFilters()" class="text-xs px-3 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-500 rounded hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                                    ✕ Hapus Semua
                                </button>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button onclick="toggleFilter('reserved')" id="filter_reserved" class="filter-btn filter-active px-4 py-2 rounded-lg font-semibold text-sm transition-all duration-200 shadow-sm hover:shadow-md" style="background-color: #3B82F6; color: white;">
                                <span class="flex items-center gap-2">
                                    <span class="checkmark">✓</span>
                                    Reserved
                                    <span class="count badge" id="count_reserved">0</span>
                                </span>
                            </button>
                            <button onclick="toggleFilter('confirmed')" id="filter_confirmed" class="filter-btn filter-active px-4 py-2 rounded-lg font-semibold text-sm transition-all duration-200 shadow-sm hover:shadow-md" style="background-color: #10B981; color: white;">
                                <span class="flex items-center gap-2">
                                    <span class="checkmark">✓</span>
                                    Confirmed
                                    <span class="count badge" id="count_confirmed">0</span>
                                </span>
                            </button>
                            <button onclick="toggleFilter('checked_in')" id="filter_checked_in" class="filter-btn filter-active px-4 py-2 rounded-lg font-semibold text-sm transition-all duration-200 shadow-sm hover:shadow-md" style="background-color: #F59E0B; color: white;">
                                <span class="flex items-center gap-2">
                                    <span class="checkmark">✓</span>
                                    Checked In
                                    <span class="count badge" id="count_checked_in">0</span>
                                </span>
                            </button>
                            <button onclick="toggleFilter('checked_out')" id="filter_checked_out" class="filter-btn filter-active px-4 py-2 rounded-lg font-semibold text-sm transition-all duration-200 shadow-sm hover:shadow-md" style="background-color: #6B7280; color: white;">
                                <span class="flex items-center gap-2">
                                    <span class="checkmark">✓</span>
                                    Checked Out
                                    <span class="count badge" id="count_checked_out">0</span>
                                </span>
                            </button>
                            <button onclick="toggleFilter('cancelled')" id="filter_cancelled" class="filter-btn filter-active px-4 py-2 rounded-lg font-semibold text-sm transition-all duration-200 shadow-sm hover:shadow-md" style="background-color: #EF4444; color: white;">
                                <span class="flex items-center gap-2">
                                    <span class="checkmark">✓</span>
                                    Cancelled
                                    <span class="count badge" id="count_cancelled">0</span>
                                </span>
                            </button>
                            <button onclick="toggleFilter('no_show')" id="filter_no_show" class="filter-btn filter-active px-4 py-2 rounded-lg font-semibold text-sm transition-all duration-200 shadow-sm hover:shadow-md" style="background-color: #9333EA; color: white;">
                                <span class="flex items-center gap-2">
                                    <span class="checkmark">✓</span>
                                    No Show
                                    <span class="count badge" id="count_no_show">0</span>
                                </span>
                            </button>
                        </div>
                        <div class="mt-3 text-xs text-gray-600 dark:text-gray-400">
                            <span class="font-semibold">Total ditampilkan:</span>
                            <span id="total_visible" class="font-bold text-indigo-600 dark:text-indigo-400">0</span>
                            dari
                            <span id="total_events" class="font-bold">0</span> reservasi
                        </div>
                    </div>

                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reservation Detail Modal -->
    <div id="reservationDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div id="modalHeader" class="px-6 py-4 rounded-t-lg flex justify-between items-center sticky top-0">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Detail Reservasi
                </h2>
                <button onclick="closeReservationDetailModal()" class="text-white hover:text-gray-200 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4">
                <!-- Status Badge -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Confirmation Number</p>
                        <p id="modal_confirmation" class="text-lg font-bold text-gray-800 dark:text-gray-200"></p>
                    </div>
                    <span id="modal_status_badge" class="px-4 py-2 rounded-full text-sm font-semibold"></span>
                </div>

                <!-- Guest Information -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded">
                    <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Informasi Tamu
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Nama Tamu</p>
                            <p id="modal_guest_name" class="font-semibold text-gray-800 dark:text-gray-200"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Jumlah Tamu</p>
                            <p id="modal_guest_count" class="font-semibold text-gray-800 dark:text-gray-200"></p>
                        </div>
                    </div>
                </div>

                <!-- Room Information -->
                <div class="bg-purple-50 dark:bg-purple-900/20 border-l-4 border-purple-500 p-4 rounded">
                    <h3 class="text-sm font-semibold text-purple-900 dark:text-purple-300 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Informasi Kamar
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Nomor Kamar</p>
                            <p id="modal_room_number" class="font-semibold text-gray-800 dark:text-gray-200"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Tipe Kamar</p>
                            <p id="modal_room_type" class="font-semibold text-gray-800 dark:text-gray-200"></p>
                        </div>
                    </div>
                </div>

                <!-- Stay Information -->
                <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded">
                    <h3 class="text-sm font-semibold text-green-900 dark:text-green-300 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Periode Menginap
                    </h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Check-in</span>
                            <span id="modal_checkin" class="font-semibold text-gray-800 dark:text-gray-200"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Check-out</span>
                            <span id="modal_checkout" class="font-semibold text-gray-800 dark:text-gray-200"></span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-green-200 dark:border-green-700">
                            <span class="text-sm font-semibold text-green-900 dark:text-green-300">Durasi</span>
                            <span id="modal_nights" class="font-bold text-green-900 dark:text-green-300"></span>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-4 rounded">
                    <h3 class="text-sm font-semibold text-yellow-900 dark:text-yellow-300 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Informasi Biaya
                    </h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Rate per Malam</span>
                            <span id="modal_room_rate" class="font-semibold text-gray-800 dark:text-gray-200"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Total Charge</span>
                            <span id="modal_total_charge" class="font-semibold text-gray-800 dark:text-gray-200"></span>
                        </div>
                        <div id="modal_deposit_container" class="flex justify-between items-center hidden">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Deposit</span>
                            <span id="modal_deposit" class="font-semibold text-gray-800 dark:text-gray-200"></span>
                        </div>
                    </div>
                </div>

                <!-- Special Requests (if any) -->
                <div id="modal_special_requests_container" class="hidden bg-gray-50 dark:bg-gray-700 p-4 rounded">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Permintaan Khusus</h3>
                    <p id="modal_special_requests" class="text-sm text-gray-600 dark:text-gray-400 italic"></p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 rounded-b-lg flex justify-between items-center sticky bottom-0">
                <a id="modal_view_folio" href="#" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-semibold text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Lihat Detail Lengkap
                </a>
                <button onclick="closeReservationDetailModal()" class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 px-6 py-2 rounded-lg transition font-semibold">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
        <style>
            /* Calendar customization */
            .fc-event {
                border: none !important;
                padding: 2px 4px !important;
                margin: 1px 0 !important;
                font-size: 0.85rem !important;
                font-weight: 500 !important;
                border-radius: 4px !important;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
                transition: transform 0.2s, box-shadow 0.2s;
            }

            .fc-event:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
                opacity: 0.95;
            }

            .fc-event-title {
                font-weight: 600 !important;
                white-space: normal !important;
                overflow: visible !important;
            }

            .fc-daygrid-event {
                white-space: normal !important;
            }

            .fc-toolbar-title {
                font-size: 1.5rem !important;
                font-weight: 700 !important;
                color: #1f2937;
            }

            .fc-button {
                background-color: #4f46e5 !important;
                border-color: #4f46e5 !important;
                text-transform: capitalize !important;
                font-weight: 600 !important;
            }

            .fc-button:hover {
                background-color: #4338ca !important;
                border-color: #4338ca !important;
            }

            .fc-button-active {
                background-color: #3730a3 !important;
                border-color: #3730a3 !important;
            }

            .fc-day-today {
                background-color: #fef3c7 !important;
            }

            /* Dark mode adjustments */
            .dark .fc-toolbar-title {
                color: #f3f4f6;
            }

            .dark .fc {
                color: #f3f4f6;
            }

            .dark .fc-scrollgrid {
                border-color: #374151 !important;
            }

            .dark .fc-col-header-cell {
                background-color: #1f2937;
                border-color: #374151 !important;
            }

            .dark .fc-daygrid-day {
                background-color: #111827;
                border-color: #374151 !important;
            }

            .dark .fc-day-today {
                background-color: #422006 !important;
            }

            /* Tooltip enhancement */
            .fc-event[title] {
                position: relative;
            }

            /* Filter button styles */
            .filter-btn {
                position: relative;
                user-select: none;
            }

            .filter-btn.filter-active {
                opacity: 1;
                transform: scale(1);
            }

            .filter-btn:not(.filter-active) {
                opacity: 0.4;
                filter: grayscale(0.5);
                transform: scale(0.95);
            }

            .filter-btn:not(.filter-active) .checkmark {
                display: none;
            }

            .filter-btn .badge {
                background-color: rgba(0, 0, 0, 0.2);
                padding: 2px 8px;
                border-radius: 12px;
                font-size: 0.75rem;
                font-weight: bold;
            }

            .filter-btn:hover {
                transform: scale(1.05) !important;
                opacity: 1 !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Daftarkan plugin chart
                Chart.register(ChartDataLabels);

                const calendarEl = document.getElementById('calendar');
                const ctx = document.getElementById('occupancyChart').getContext('2d');
                let occupancyChart;

                // Filter state management
                let activeFilters = new Set(['reserved', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show']);
                let allEvents = [];
                let eventCounts = {};

                // Inisialisasi FullCalendar
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    eventClick: function(info) {
                        info.jsEvent.preventDefault();
                        showReservationDetail(info.event);
                    },
                    eventDidMount: function(info) {
                        // Add tooltip on hover
                        const props = info.event.extendedProps;
                        info.el.title = `${props.guestName}\n${props.roomNumber} - ${props.roomType}\nConfirmation: ${props.confirmationNumber}\nStatus: ${props.status}`;

                        // Add cursor pointer
                        info.el.style.cursor = 'pointer';
                    },
                    eventContent: function(arg) {
                        // Customize event display
                        let html = '<div class="fc-event-main-frame">';
                        html += '<div class="fc-event-title-container">';
                        html += '<div class="fc-event-title fc-sticky">';
                        html += arg.event.title;
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';

                        return { html: html };
                    }
                });
                
                // Fungsi untuk merender chart
                function renderChart(chartData) {
                    if (occupancyChart) {
                        occupancyChart.data.labels = chartData.labels;
                        occupancyChart.data.datasets[0].data = chartData.data;
                        occupancyChart.update();
                    } else {
                        occupancyChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: chartData.labels,
                                datasets: [{
                                    label: 'Kamar Terisi',
                                    data: chartData.data,
                                    borderColor: 'rgba(79, 70, 229, 1)',
                                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                    fill: true,
                                    tension: 0.3
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: { y: { beginAtZero: true } },
                                plugins: {
                                    datalabels: {
                                        anchor: 'end', align: 'end', color: '#555',
                                        font: { weight: 'bold' },
                                        formatter: (value) => value > 0 ? value : '',
                                        backgroundColor: 'rgba(255, 255, 255, 0.7)',
                                        borderRadius: 4, padding: 4
                                    }
                                }
                            }
                        });
                    }
                }
                
                // Fungsi untuk mengambil semua data (event kalender dan data chart)
                function fetchData() {
                    // Gunakan route yang benar untuk 'pengguna_properti'
                    fetch("{{ route('property.calendar.data') }}")
                        .then(response => response.json())
                        .then(data => {
                            // Store all events
                            allEvents = data.events;

                            // Count events by status
                            eventCounts = {};
                            allEvents.forEach(event => {
                                const status = event.extendedProps.status.toLowerCase().replace(' ', '_');
                                eventCounts[status] = (eventCounts[status] || 0) + 1;
                            });

                            // Update count badges
                            updateFilterCounts();

                            // Apply filters and display
                            applyFilters();

                            if (data.chartData) {
                                renderChart(data.chartData);
                            }
                        })
                        .catch(error => console.error('Error fetching data:', error));
                }

                // Update filter count badges
                function updateFilterCounts() {
                    const statuses = ['reserved', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'];
                    let totalCount = 0;

                    statuses.forEach(status => {
                        const count = eventCounts[status] || 0;
                        const badge = document.getElementById(`count_${status}`);
                        if (badge) {
                            badge.textContent = count;
                        }
                        totalCount += count;
                    });

                    document.getElementById('total_events').textContent = totalCount;
                }

                // Apply active filters to calendar
                function applyFilters() {
                    // Filter events based on active filters
                    const filteredEvents = allEvents.filter(event => {
                        const status = event.extendedProps.status.toLowerCase().replace(' ', '_');
                        return activeFilters.has(status);
                    });

                    // Update calendar
                    calendar.removeAllEvents();
                    calendar.addEventSource(filteredEvents);

                    // Update total visible count
                    document.getElementById('total_visible').textContent = filteredEvents.length;
                }

                // Toggle filter on/off
                window.toggleFilter = function(status) {
                    const button = document.getElementById(`filter_${status}`);

                    if (activeFilters.has(status)) {
                        activeFilters.delete(status);
                        button.classList.remove('filter-active');
                    } else {
                        activeFilters.add(status);
                        button.classList.add('filter-active');
                    }

                    applyFilters();
                }

                // Select all filters
                window.selectAllFilters = function() {
                    const statuses = ['reserved', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'];
                    statuses.forEach(status => {
                        activeFilters.add(status);
                        document.getElementById(`filter_${status}`).classList.add('filter-active');
                    });
                    applyFilters();
                }

                // Clear all filters
                window.clearAllFilters = function() {
                    const statuses = ['reserved', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'];
                    statuses.forEach(status => {
                        activeFilters.delete(status);
                        document.getElementById(`filter_${status}`).classList.remove('filter-active');
                    });
                    applyFilters();
                }

                // Render kalender dan ambil data awal saat halaman dimuat
                calendar.render();
                fetchData();
            });

            // Modal Functions
            function showReservationDetail(event) {
                const props = event.extendedProps;
                const modal = document.getElementById('reservationDetailModal');
                const modalHeader = document.getElementById('modalHeader');

                // Format dates
                const startDate = new Date(event.start);
                const endDate = new Date(event.end);

                const formatDate = (date) => {
                    return date.toLocaleDateString('id-ID', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                };

                // Set header color based on status
                const statusColors = {
                    'reserved': 'bg-blue-600',
                    'confirmed': 'bg-green-600',
                    'checked_in': 'bg-orange-600',
                    'checked_out': 'bg-gray-600',
                    'cancelled': 'bg-red-600',
                    'no_show': 'bg-purple-600'
                };

                const statusLabels = {
                    'reserved': 'Reserved',
                    'confirmed': 'Confirmed',
                    'checked_in': 'Checked In',
                    'checked_out': 'Checked Out',
                    'cancelled': 'Cancelled',
                    'no_show': 'No Show'
                };

                const statusKey = props.status.toLowerCase().replace(' ', '_');
                modalHeader.className = `px-6 py-4 rounded-t-lg flex justify-between items-center sticky top-0 ${statusColors[statusKey] || 'bg-gray-600'}`;

                // Populate modal fields
                document.getElementById('modal_confirmation').textContent = props.confirmationNumber || '-';
                document.getElementById('modal_guest_name').textContent = props.guestName || '-';
                document.getElementById('modal_guest_count').textContent = `${props.adults || 0} dewasa` + (props.children > 0 ? `, ${props.children} anak` : '');
                document.getElementById('modal_room_number').textContent = props.roomNumber || '-';
                document.getElementById('modal_room_type').textContent = props.roomType || '-';
                document.getElementById('modal_checkin').textContent = formatDate(startDate);
                document.getElementById('modal_checkout').textContent = formatDate(endDate);
                document.getElementById('modal_nights').textContent = `${props.nights || 0} malam`;
                document.getElementById('modal_room_rate').textContent = props.roomRate || '-';
                document.getElementById('modal_total_charge').textContent = props.totalCharge || '-';

                // Status badge
                const statusBadge = document.getElementById('modal_status_badge');
                statusBadge.textContent = statusLabels[statusKey] || props.status;
                statusBadge.className = `px-4 py-2 rounded-full text-sm font-semibold text-white ${statusColors[statusKey] || 'bg-gray-600'}`;

                // Deposit (optional)
                const depositContainer = document.getElementById('modal_deposit_container');
                if (props.deposit && props.deposit !== '-' && props.deposit !== 'Rp 0') {
                    depositContainer.classList.remove('hidden');
                    document.getElementById('modal_deposit').textContent = props.deposit;
                } else {
                    depositContainer.classList.add('hidden');
                }

                // Special requests (optional)
                const specialRequestsContainer = document.getElementById('modal_special_requests_container');
                if (props.specialRequests && props.specialRequests !== '-') {
                    specialRequestsContainer.classList.remove('hidden');
                    document.getElementById('modal_special_requests').textContent = props.specialRequests;
                } else {
                    specialRequestsContainer.classList.add('hidden');
                }

                // Set view folio link
                document.getElementById('modal_view_folio').href = '/frontoffice';

                // Show modal
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeReservationDetailModal() {
                const modal = document.getElementById('reservationDetailModal');
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }

            // Close modal when clicking outside
            document.getElementById('reservationDetailModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeReservationDetailModal();
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeReservationDetailModal();
                }
            });
        </script>
    @endpush
</x-app-layout>