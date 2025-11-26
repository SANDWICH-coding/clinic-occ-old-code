<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - OCC Health System</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <style>
        :root {
            --primary-blue: #1e40af;
            --primary-dark: #1e3a8a;
            --primary-light: #dbeafe;
            --accent-teal: #0d9488;
            --accent-amber: #f59e0b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-800: #1f2937;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            min-height: 100vh;
        }
        
        /* Enhanced Header */
        .main-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-dark) 100%);
            box-shadow: 0 4px 20px rgba(30, 64, 175, 0.3);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }
        
        .logo-container {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo-image {
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            border: 3px solid rgba(255, 255, 255, 0.2);
        }
        
        .logo-badge {
            position: absolute;
            bottom: -5px;
            right: -5px;
            width: 18px;
            height: 18px;
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        /* Flexible Layout System */
        .page-container {
            min-height: calc(100vh - 80px);
            display: flex;
            flex-direction: column;
        }
        
        .content-area {
            flex: 1;
            padding: 2rem 0;
        }
        
        /* Dashboard Specific Styles */
        .dashboard-container {
            background-color: #f8fafc;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border-left: 4px solid var(--primary-blue);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .chart-container {
            position: relative;
            height: 280px;
            width: 100%;
            min-height: 250px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .card-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        /* General Page Styles */
        .page-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        /* Loading States */
        .loading-spinner {
            border: 3px solid #f3f4f6;
            border-left: 3px solid var(--primary-blue);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Grid Layouts */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        /* Header Styles */
        .section-header {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        /* Alert & Badge Styles */
        .alert-badge {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: var(--danger);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }
        
        .badge-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        
        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--primary-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        
        .refresh-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
        }
        
        .refresh-btn:hover {
            background: #f3f4f6;
            color: #374151;
        }
        
        .logout-btn {
            background-color: var(--danger);
            color: white;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background-color: #b91c1c;
            transform: translateY(-1px);
        }
        
        /* Mobile Navigation */
        .mobile-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
            padding: 0.75rem;
            display: none;
            z-index: 50;
        }
        
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.5rem;
            border-radius: 0.5rem;
            color: #6b7280;
            text-decoration: none;
            font-size: 0.75rem;
            transition: all 0.2s ease;
        }
        
        .nav-item.active {
            background: var(--primary-blue);
            color: white;
        }
        
        .nav-item:hover {
            background: #f3f4f6;
        }
        
        .nav-item.active:hover {
            background: var(--primary-dark);
        }
        
        /* Toast Notification */
        .toast {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: #1f2937;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 100;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .charts-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .chart-container {
                height: 250px;
            }
            
            .section-header {
                font-size: 1.125rem;
                margin-bottom: 1rem;
            }
            
            .mobile-nav {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
            }
            
            .main-content {
                padding-bottom: 5rem;
            }
            
            .chart-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            
            .header-actions {
                flex-direction: column;
                gap: 0.75rem;
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .stat-card {
                padding: 1.25rem;
            }
            
            .chart-container {
                height: 220px;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .dashboard-container {
                background-color: #111827;
            }
            
            .stat-card,
            .dashboard-card,
            .page-card {
                background: #1f2937;
                color: #f9fafb;
                border-color: #374151;
            }
            
            .section-header {
                color: #f9fafb;
                border-bottom-color: #374151;
            }
        }
        
        /* Print Styles */
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body class="antialiased">
    <div class="min-h-screen flex flex-col">
        <!-- Enhanced Header -->
        <header class="main-header">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center h-20">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-4 group">
                        <div class="logo-container">
                            <img src="https://scontent-lga3-2.xx.fbcdn.net/v/t39.30808-1/510989554_1291512086313864_1697600367017083003_n.jpg?stp=c31.31.1500.1500a_dst-jpg_s200x200_tt6&_nc_cat=107&ccb=1-7&_nc_sid=2d3e12&_nc_ohc=GIHo1IZXBeYQ7kNvwF76pSD&_nc_oc=AdmsNqHTttqDxS8cbetmhq1bF0IJYtYAgzpraUjkBQJZoKelk0sid61CYlRlN1GvHAmG6doTOpmMdvWU0N_pSmyD&_nc_zt=24&_nc_ht=scontent-lga3-2.xx&_nc_gid=gelh9Ijv3v3aEEZOm2y53w&oh=00_AfjnuBgycKGhOYAVkp9qIgsko3JWnVwZye9sKHINulD0ag&oe=6924E8A5" 
                                 alt="OCC Logo" 
                                 class="h-12 w-12 logo-image object-cover">
                            <!-- <div class="logo-badge"></div> -->
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-white">OCC Health System</h1>
                            <p class="text-sm text-blue-100 opacity-90 hidden sm:block">Comprehensive Health Management Platform</p>
                        </div>
                    </a>

                    <div class="flex items-center gap-4">
                        @auth
                            <div class="flex items-center gap-4">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="logout-btn btn no-print px-4 py-2 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="page-container">
            <div class="content-area">
                @yield('content')
            </div>
        </main>

      

       

    <!-- Global JavaScript -->
    <script>
        // Toast notification function
        function showToast(message, type = 'info', duration = 4000) {
            const toast = document.getElementById('toast');
            const typeClass = type === 'success' ? 'toast-success' : 
                            type === 'error' ? 'toast-error' : '';
            
            let icon = '';
            if (type === 'success') {
                icon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
            } else if (type === 'error') {
                icon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            } else {
                icon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            }
            
            toast.className = `toast no-print ${typeClass}`;
            toast.innerHTML = `${icon}<span>${message}</span>`;
            
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, duration);
        }
        
        // Global refresh function
        function refreshDashboard() {
            showToast('Refreshing dashboard data...', 'info', 2000);
            // Add actual refresh logic here
            setTimeout(() => {
                showToast('Dashboard updated successfully', 'success');
            }, 1000);
        }
        
        // Export function
        function exportReport(format) {
            showToast(`Generating ${format.toUpperCase()} report...`, 'info');
            // Add actual export logic here
        }
        
        // Initialize common UI components
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to cards on load
            const cards = document.querySelectorAll('.stat-card, .dashboard-card, .page-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
        
        // Pusher Error Handler
        (function() {
            const originalConsoleError = console.error;
            console.error = function(...args) {
                if (args[0] && typeof args[0] === 'string' && args[0].includes('Pusher')) {
                    console.warn('Pusher: Real-time features temporarily disabled');
                    return;
                }
                originalConsoleError.apply(console, args);
            };

            if (typeof Pusher !== 'undefined') {
                window.Pusher = class SafePusher {
                    constructor(key, options) {
                        console.warn('Pusher: Running in safe mode - real-time features disabled');
                    }
                    static logToConsole = false;
                    subscribe() { return this; }
                    bind() { return this; }
                    unbind() { return this; }
                };
            }
        })();
    </script>
    
    @stack('scripts')
</body>
</html>