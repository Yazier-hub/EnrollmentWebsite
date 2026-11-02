const pageData = JSON.parse(document.getElementById('page-data').dataset.values);

document.addEventListener('DOMContentLoaded', function () {

    // ============================================================
    // EXPORT DROPDOWN TOGGLE
    // ============================================================
    window.toggleExportMenu = function() {
        var menu = document.getElementById('exportMenu');
        menu.classList.toggle('show');
    };

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        var dropdown = document.querySelector('.export-dropdown');
        var menu = document.getElementById('exportMenu');
        if (dropdown && !dropdown.contains(e.target)) {
            menu.classList.remove('show');
        }
    });

    // ============================================================
    // SHARED THEME
    // ============================================================
    Chart.defaults.font.family = "'Segoe UI', 'Helvetica Neue', Arial, sans-serif";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#64748b';
    Chart.defaults.padding     = 10;

    const tooltipStyle = {
        backgroundColor: 'rgba(15, 23, 42, 0.95)',
        titleColor:      '#ffffff',
        bodyColor:       '#e2e8f0',
        borderColor:     'rgba(148, 163, 184, 0.2)',
        borderWidth:     1,
        padding:         12,
        cornerRadius:    8,
        titleFont:       { size: 13, weight: '600' },
        bodyFont:        { size: 12 },
        displayColors:   true,
        boxPadding:      6
    };

    const legendStyle = {
        position: 'bottom',
        labels: {
            padding:       16,
            usePointStyle: true,
            pointStyle:    'circle',
            boxWidth:      8,
            boxHeight:     8,
            font:          { size: 11, weight: '500' },
            color:         '#475569'
        }
    };

    const gridColor     = 'rgba(148, 163, 184, 0.12)';
    const gridColorSoft = 'rgba(148, 163, 184, 0.06)';

    // ============================================================
    // 1. ENROLLMENT TREND
    // ============================================================
    const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');

    const enrollmentGradient = enrollmentCtx.createLinearGradient(0, 0, 0, 280);
    enrollmentGradient.addColorStop(0,   'rgba(59, 130, 246, 0.3)');
    enrollmentGradient.addColorStop(0.6, 'rgba(59, 130, 246, 0.08)');
    enrollmentGradient.addColorStop(1,   'rgba(59, 130, 246, 0)');

    new Chart(enrollmentCtx, {
        type: 'line',
        data: {
            labels: pageData.months,
            datasets: [{
                label: 'Subject Enrollments',
                data: pageData.enrollmentTrend,
                borderColor:     '#3b82f6',
                backgroundColor: enrollmentGradient,
                borderWidth:     2.5,
                fill:            true,
                tension:         0.4,
                pointRadius:     0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#3b82f6',
                pointHoverBorderColor:     '#ffffff',
                pointHoverBorderWidth:     2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend:  { display: false },
                tooltip: {
                    ...tooltipStyle,
                    callbacks: {
                        title: (items) => items[0].label + ' ' + pageData.currentYear,
                        label: (ctx)   => '  ' + ctx.parsed.y + ' enrollments'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0, color: '#94a3b8', padding: 8 },
                    grid: { color: gridColor, drawBorder: false, drawTicks: false },
                    border: { display: false }
                },
                x: {
                    ticks: { color: '#94a3b8', padding: 8 },
                    grid: { display: false },
                    border: { display: false }
                }
            }
        }
    });

    // ============================================================
    // 2. COURSE DISTRIBUTION
    // ============================================================
    const courseCtx    = document.getElementById('courseChart').getContext('2d');
    const courseData   = pageData.enrollmentByCourse;
    const courseLabels = courseData.map(item => item.course_code || 'Unknown');
    const courseCounts = courseData.map(item => parseInt(item.student_count, 10) || 0);
    const totalCourses = courseCounts.reduce((a, b) => a + b, 0);
    const hasCourseData = courseCounts.some(v => v > 0);

    const courseColors = [
        '#3b82f6', '#10b981', '#f59e0b', '#ef4444',
        '#06b6d4', '#8b5cf6', '#f97316', '#14b8a6'
    ];

    const centerLabelPlugin = {
        id: 'centerLabel',
        beforeDraw: function (chart) {
            if (!hasCourseData) return;
            const { ctx, chartArea } = chart;
            if (!chartArea) return;

            const cx = (chartArea.left + chartArea.right) / 2;
            const cy = (chartArea.top + chartArea.bottom) / 2;

            ctx.save();
            ctx.fillStyle = '#0f172a';
            ctx.font = 'bold 24px "Segoe UI", sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(totalCourses, cx, cy - 6);

            ctx.fillStyle = '#64748b';
            ctx.font = '10px "Segoe UI", sans-serif';
            ctx.fillText('STUDENTS', cx, cy + 16);
            ctx.restore();
        }
    };

    new Chart(courseCtx, {
        type: 'doughnut',
        data: {
            labels: courseLabels.length > 0 ? courseLabels : ['No Data'],
            datasets: [{
                data: hasCourseData ? courseCounts : [1],
                backgroundColor: hasCourseData
                    ? courseColors.slice(0, courseCounts.length)
                    : ['#e2e8f0'],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: hasCourseData ? legendStyle : { display: false },
                tooltip: {
                    ...tooltipStyle,
                    callbacks: {
                        label: (ctx) => {
                            if (!hasCourseData) return '  No data';
                            const value = ctx.parsed;
                            const pct = ((value / totalCourses) * 100).toFixed(1);
                            return '  ' + ctx.label + ': ' + value + ' (' + pct + '%)';
                        }
                    }
                }
            }
        },
        plugins: [centerLabelPlugin]
    });

    // ============================================================
    // 3. APPLICATIONS TREND
    // ============================================================
    const appCtx = document.getElementById('applicationChart').getContext('2d');

    const appGradient = appCtx.createLinearGradient(0, 0, 0, 280);
    appGradient.addColorStop(0, '#06b6d4');
    appGradient.addColorStop(1, '#0e7490');

    new Chart(appCtx, {
        type: 'bar',
        data: {
            labels: pageData.months,
            datasets: [{
                label: 'Applications',
                data: pageData.applicationTrend,
                backgroundColor: appGradient,
                hoverBackgroundColor: '#0891b2',
                borderRadius: 6,
                borderSkipped: false,
                barPercentage: 0.6,
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...tooltipStyle,
                    callbacks: {
                        title: (items) => items[0].label + ' ' + pageData.currentYear,
                        label: (ctx)   => '  ' + ctx.parsed.y + ' applications'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0, color: '#94a3b8', padding: 8 },
                    grid: { color: gridColor, drawBorder: false, drawTicks: false },
                    border: { display: false }
                },
                x: {
                    ticks: { color: '#94a3b8', padding: 8 },
                    grid: { display: false },
                    border: { display: false }
                }
            }
        }
    });

    // ============================================================
    // 4. YEAR LEVEL DISTRIBUTION
    // ============================================================
    const yearCtx    = document.getElementById('yearChart').getContext('2d');
    const yearData   = pageData.enrollmentByYear;
    const yearLabels = yearData.map(item => item.grade_level || 'Unknown');
    const yearCounts = yearData.map(item => parseInt(item.student_count, 10) || 0);
    const hasYearData = yearCounts.some(v => v > 0);

    const valueLabelsPlugin = {
        id: 'valueLabels',
        afterDatasetsDraw: function (chart) {
            if (!hasYearData) return;
            const { ctx } = chart;
            chart.data.datasets.forEach(function (dataset, i) {
                const meta = chart.getDatasetMeta(i);
                meta.data.forEach(function (bar, index) {
                    const value = dataset.data[index];
                    if (value === 0) return;
                    ctx.save();
                    ctx.fillStyle  = '#0f172a';
                    ctx.font       = 'bold 11px "Segoe UI", sans-serif';
                    ctx.textAlign  = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillText(value, bar.x, bar.y - 4);
                    ctx.restore();
                });
            });
        }
    };

    new Chart(yearCtx, {
        type: 'bar',
        data: {
            labels: yearLabels.length > 0 ? yearLabels : ['No Data'],
            datasets: [{
                label: 'Students',
                data: hasYearData ? yearCounts : [0],
                backgroundColor: hasYearData
                    ? ['#3b82f6', '#10b981', '#f59e0b', '#ef4444']
                    : ['#e2e8f0'],
                hoverBackgroundColor: hasYearData
                    ? ['#2563eb', '#059669', '#d97706', '#dc2626']
                    : ['#cbd5e1'],
                borderRadius: 8,
                borderSkipped: false,
                barPercentage: 0.55,
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: { padding: { top: 20 } },
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...tooltipStyle,
                    callbacks: { label: (ctx) => '  ' + ctx.parsed.y + ' students' }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: hasYearData ? Math.max(...yearCounts) + 1 : 1,
                    ticks: { stepSize: 1, precision: 0, color: '#94a3b8', padding: 8 },
                    grid: { color: gridColorSoft, drawBorder: false, drawTicks: false },
                    border: { display: false }
                },
                x: {
                    ticks: { color: '#94a3b8', padding: 8 },
                    grid: { display: false },
                    border: { display: false }
                }
            }
        },
        plugins: [valueLabelsPlugin]
    });

});

// Event bindings for this page; dynamic values come from HTML data attributes.
PageUI.bind('click', {
    action1: function (event) {
        toggleExportMenu()
    }
});
