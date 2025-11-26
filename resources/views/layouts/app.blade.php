<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - OPOL COMMUNITY COLLEGE CLINIC</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS Only -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        @keyframes dropdown {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        @keyframes slideInToast {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .animate-slideIn {
            animation: slideIn 0.3s ease-out;
        }
        .animate-dropdown {
            animation: dropdown 0.2s ease-out;
        }
        .animate-slideInToast {
            animation: slideInToast 0.3s ease-out;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 5px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        /* Logo styling */
        .logo-placeholder {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border-radius: 0.75rem;
        }
    </style>
</head>
<body class="antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo & Brand -->
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                        <div class="relative">
                            <!-- Logo with fallback -->
                            <img src="https://scontent.fmnl13-4.fna.fbcdn.net/v/t39.30808-6/510989554_1291512086313864_1697600367017083003_n.jpg?_nc_cat=107&ccb=1-7&_nc_sid=6ee11a&_nc_ohc=mvFfUBM-k9EQ7kNvwEjttV0&_nc_oc=AdkTZ6gHs6e82Rebe2mTd9BqUbjJa6w8a978MXJsxRi_9GmGB_3K-vFOxy_Fot6qA-qoNWy3mFE0J16s7SFfZ5vf&_nc_zt=23&_nc_ht=scontent.fmnl13-4.fna&_nc_gid=GWM4j-IN5o5nsKJNAhaO1w&oh=00_AfiQT11yCbLwYxlCZtuujPdEH5tZaLe9rQ0aqQ51lh-hzA&oe=692A4E27" 
                                 alt="OCC Logo" 
                                 class="h-10 w-10 rounded-xl object-cover ring-2 ring-blue-100 group-hover:ring-blue-300 transition-all"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="h-10 w-10 logo-placeholder ring-2 ring-blue-100 group-hover:ring-blue-300 transition-all hidden">
                                OCC
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                        </div>
                        <div>
                            <span class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">OCC clinic Management System</span>
                            <p class="text-xs text-gray-500 hidden sm:block">OPOL COMMUNITY COLLEGE</p>
                        </div>
                    </a>

                    <!-- User Menu -->
                    <div class="flex items-center gap-3">
                        @auth
                            <!-- Notification Bell -->
                            <div class="relative">
                                <button id="notificationButton" 
                                        class="flex items-center p-2 rounded-xl hover:bg-gray-100 transition-colors group relative">
                                    <svg class="w-6 h-6 text-gray-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                    <span id="notificationBadge" class="hidden absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">0</span>
                                </button>
                                
                                <!-- Notification Dropdown -->
                                <div id="notificationDropdown" 
                                     class="hidden absolute right-0 mt-2 w-96 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50 animate-dropdown max-h-[600px] flex flex-col">
                                    <!-- Header -->
                                    <div class="px-4 py-3 bg-gradient-to-br from-blue-50 to-blue-100 border-b border-blue-200 flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">Notifications</p>
                                            <p id="unreadCountText" class="text-xs text-gray-600">0 unread</p>
                                        </div>
                                        <button id="markAllReadBtn" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                            Mark all read
                                        </button>
                                    </div>
                                    
                                    <!-- Notification Items -->
                                    <div id="notificationList" class="divide-y divide-gray-100 overflow-y-auto flex-1">
                                        <div class="p-8 text-center text-gray-500">
                                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                            </svg>
                                            <p class="text-sm">No notifications yet</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Footer -->
                                    <div class="border-t border-gray-100">
                                        <button id="clearReadBtn" class="w-full px-4 py-3 text-sm text-gray-600 hover:bg-gray-50 transition-colors text-center font-medium">
                                            Clear read notifications
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- User Dropdown -->
                            <div class="relative">
                                <button id="userMenuButton" 
                                        class="flex items-center gap-3 px-4 py-2 rounded-xl hover:bg-gray-100 transition-colors group">
                                    <div class="hidden sm:block text-right">
                                        <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                        <p class="text-xs text-gray-500">{{ ucfirst(auth()->user()->role ?? 'Student') }}</p>
                                    </div>
                                    <div class="relative">
                                        <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold shadow-sm group-hover:shadow-md transition-shadow">
                                            {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div id="userDropdown" 
                                     class="hidden absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50 animate-dropdown">
                                    <!-- User Info Header -->
                                    <div class="px-4 py-4 bg-gradient-to-br from-blue-50 to-blue-100 border-b border-blue-200">
                                        <div class="flex items-center gap-3">
                                            <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                                                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-gray-900 truncate">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                                <p class="text-xs text-gray-600 truncate">{{ auth()->user()->email }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Menu Items -->
                                    <div class="py-2">
                                        <a href="{{ route('student.profile') }}" 
                                           class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 transition-colors group">
                                            <div class="w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 flex items-center justify-center transition-colors">
                                                <svg class="w-4 h-4 text-gray-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <span class="font-medium">My Profile</span>
                                        </a>
                                        
                                        <a href="{{ route('student.academic-info') }}" 
                                           class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 transition-colors group">
                                            <div class="w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 flex items-center justify-center transition-colors">
                                                <svg class="w-4 h-4 text-gray-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                </svg>
                                            </div>
                                            <span class="font-medium">Academic Info</span>
                                        </a>
                                        
                                        <a href="{{ route('student.change-password') }}" 
                                           class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 transition-colors group">
                                            <div class="w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-blue-100 flex items-center justify-center transition-colors">
                                                <svg class="w-4 h-4 text-gray-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                                </svg>
                                            </div>
                                            <span class="font-medium">Change Password</span>
                                        </a>
                                    </div>
                                    
                                    <!-- Logout -->
                                    <div class="border-t border-gray-100">
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors group">
                                                <div class="w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-red-100 flex items-center justify-center transition-colors">
                                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                    </svg>
                                                </div>
                                                <span class="font-medium">Logout</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-3">
                                <a href="{{ route('login') }}" 
                                   class="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-blue-600 transition-colors">
                                    Login
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" 
                                       class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition-all">
                                        Register
                                    </a>
                                @endif
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow">
            <!-- Flash Messages -->
            <div class="container mx-auto px-4 pt-6">
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-2xl p-4 flex items-start gap-3 animate-slideIn">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-green-900">Success!</p>
                            <p class="text-sm text-green-700 mt-1">{{ session('success') }}</p>
                        </div>
                        <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-green-400 hover:text-green-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('error') || $errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4 flex items-start gap-3 animate-slideIn">
                        <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-red-900">Error!</p>
                            @if(session('error'))
                                <p class="text-sm text-red-700 mt-1">{{ session('error') }}</p>
                            @endif
                            @foreach ($errors->all() as $error)
                                <p class="text-sm text-red-700 mt-1">{{ $error }}</p>
                            @endforeach
                        </div>
                        <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-red-400 hover:text-red-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3 animate-slideIn">
                        <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-amber-900">Warning!</p>
                            <p class="text-sm text-amber-700 mt-1">{{ session('warning') }}</p>
                        </div>
                        <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-amber-400 hover:text-amber-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif
            </div>

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12">
            <div class="container mx-auto px-4 py-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <img src="https://scontent.fmnl13-4.fna.fbcdn.net/v/t39.30808-6/510989554_1291512086313864_1697600367017083003_n.jpg?_nc_cat=107&ccb=1-7&_nc_sid=6ee11a&_nc_ohc=mvFfUBM-k9EQ7kNvwEjttV0&_nc_oc=AdkTZ6gHs6e82Rebe2mTd9BqUbjJa6w8a978MXJsxRi_9GmGB_3K-vFOxy_Fot6qA-qoNWy3mFE0J16s7SFfZ5vf&_nc_zt=23&_nc_ht=scontent.fmnl13-4.fna&_nc_gid=GWM4j-IN5o5nsKJNAhaO1w&oh=00_AfiQT11yCbLwYxlCZtuujPdEH5tZaLe9rQ0aqQ51lh-hzA&oe=692A4E27" 
                             alt="OCC Logo" 
                             class="h-8 w-8 rounded-lg object-cover">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Opol Community College</p>
                            <p class="text-xs text-gray-500">Health Management System</p>
                        </div>
                    </div>
                    <div class="text-center md:text-right">
                        <p class="text-sm text-gray-600">
                            &copy; {{ date('Y') }} OCC Clinic. All rights reserved.
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Made with care for student health
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Scripts -->
    <script>
        // Dropdown Toggle
        document.addEventListener('DOMContentLoaded', function() {
            // User Dropdown
            const userMenuButton = document.getElementById('userMenuButton');
            const userDropdown = document.getElementById('userDropdown');
            
            if (userMenuButton && userDropdown) {
                userMenuButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('hidden');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!userDropdown.contains(e.target) && !userMenuButton.contains(e.target)) {
                        userDropdown.classList.add('hidden');
                    }
                });
                
                // Close on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !userDropdown.classList.contains('hidden')) {
                        userDropdown.classList.add('hidden');
                    }
                });
            }

            // Notification Dropdown
            const notificationButton = document.getElementById('notificationButton');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationList = document.getElementById('notificationList');
            const notificationBadge = document.getElementById('notificationBadge');
            const unreadCountText = document.getElementById('unreadCountText');
            const markAllReadBtn = document.getElementById('markAllReadBtn');
            const clearReadBtn = document.getElementById('clearReadBtn');
            
            let notifications = [];
            let unreadCount = 0;

            // Toggle dropdown
            notificationButton?.addEventListener('click', function(e) {
                e.stopPropagation();
                const isHidden = notificationDropdown.classList.contains('hidden');
                notificationDropdown.classList.toggle('hidden');
                if (isHidden) {
                    loadNotifications();
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!notificationDropdown.contains(e.target) && !notificationButton.contains(e.target)) {
                    notificationDropdown.classList.add('hidden');
                }
            });
            
            // Close on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !notificationDropdown.classList.contains('hidden')) {
                    notificationDropdown.classList.add('hidden');
                }
            });

            // Load notifications
            function loadNotifications() {
                fetch('{{ route('notifications.index') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            notifications = data.notifications;
                            unreadCount = data.unread_count;
                            updateNotificationUI();
                        }
                    })
                    .catch(error => console.error('Error loading notifications:', error));
            }

            // Update notification count
            function updateUnreadCount() {
                fetch('{{ route('notifications.unread-count') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            unreadCount = data.count;
                            updateBadge();
                        }
                    })
                    .catch(error => console.error('Error updating count:', error));
            }

            // Update UI
            function updateNotificationUI() {
                updateBadge();
                renderNotifications();
            }

            function updateBadge() {
                if (unreadCount > 0) {
                    notificationBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    notificationBadge.classList.remove('hidden');
                    unreadCountText.textContent = `${unreadCount} unread`;
                } else {
                    notificationBadge.classList.add('hidden');
                    unreadCountText.textContent = 'All caught up!';
                }
            }

            function renderNotifications() {
                if (notifications.length === 0) {
                    notificationList.innerHTML = `
                        <div class="p-8 text-center text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <p class="text-sm">No notifications yet</p>
                        </div>
                    `;
                    return;
                }

                notificationList.innerHTML = notifications.map(notification => {
                    const data = notification.data;
                    const isUnread = !notification.read_at;
                    const timeAgo = formatTimeAgo(notification.created_at);
                    const colorClass = getColorClass(data.color);
                    
                    return `
                        <a href="${data.url}" 
                           class="block px-4 py-3 hover:bg-blue-50 transition-colors ${isUnread ? 'bg-blue-50' : ''}"
                           data-notification-id="${notification.id}"
                           onclick="markAsRead('${notification.id}', event)">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-lg ${colorClass} flex items-center justify-center">
                                        <i class="fas fa-${data.icon} text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-semibold text-gray-900 truncate">${data.title}</p>
                                        ${isUnread ? '<div class="w-2 h-2 bg-blue-600 rounded-full"></div>' : ''}
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">${data.message}</p>
                                    <p class="text-xs text-gray-500 mt-1">${timeAgo}</p>
                                </div>
                            </div>
                        </a>
                    `;
                }).join('');
            }

            function getColorClass(color) {
                const colors = {
                    'blue': 'bg-blue-500',
                    'green': 'bg-green-500',
                    'red': 'bg-red-500',
                    'yellow': 'bg-yellow-500',
                    'gray': 'bg-gray-500'
                };
                return colors[color] || 'bg-gray-500';
            }

            function formatTimeAgo(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const seconds = Math.floor((now - date) / 1000);
                
                if (seconds < 60) return 'Just now';
                if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
                if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
                if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
                return date.toLocaleDateString();
            }

            // Mark as read
            window.markAsRead = function(notificationId, event) {
                event.preventDefault();
                
                fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const notification = notifications.find(n => n.id === notificationId);
                        if (notification) {
                            notification.read_at = new Date().toISOString();
                            unreadCount = Math.max(0, unreadCount - 1);
                            updateNotificationUI();
                            // Navigate to URL
                            const notificationData = notification.data;
                            if (notificationData.url) {
                                window.location.href = notificationData.url;
                            }
                        }
                    }
                })
                .catch(error => console.error('Error marking notification as read:', error));
            };

            // Mark all as read
            markAllReadBtn?.addEventListener('click', function() {
                fetch('{{ route('notifications.mark-all-read') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notifications.forEach(n => n.read_at = new Date().toISOString());
                        unreadCount = 0;
                        updateNotificationUI();
                    }
                })
                .catch(error => console.error('Error marking all as read:', error));
            });

            // Clear read notifications
            clearReadBtn?.addEventListener('click', function() {
                fetch('{{ route('notifications.clear-read') }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notifications = notifications.filter(n => !n.read_at);
                        updateNotificationUI();
                    }
                })
                .catch(error => console.error('Error clearing read notifications:', error));
            });

            // Initial load
            updateUnreadCount();
            
            // Poll for new notifications every 30 seconds
            setInterval(updateUnreadCount, 30000);

            @auth
            // Real-time notifications with Laravel Echo (if configured)
            if (typeof Echo !== 'undefined' && Echo) {
                try {
                    Echo.private('App.Models.User.{{ auth()->id() }}')
                        .notification((notification) => {
                            // Add new notification to the list
                            notifications.unshift(notification);
                            unreadCount++;
                            updateNotificationUI();
                            
                            // Show toast notification
                            showToast(notification.data.title, notification.data.message, notification.data.color);
                        });
                } catch (error) {
                    console.log('Echo not configured - using polling instead');
                }
            }
            @endauth

            // Toast notification function
            function showToast(title, message, color = 'blue') {
                const colorClasses = {
                    'blue': 'bg-blue-500',
                    'green': 'bg-green-500',
                    'red': 'bg-red-500',
                    'yellow': 'bg-yellow-500'
                };
                
                const toast = document.createElement('div');
                toast.className = `fixed bottom-4 right-4 ${colorClasses[color] || 'bg-blue-500'} text-white px-6 py-4 rounded-lg shadow-lg z-50 animate-slideInToast max-w-sm`;
                toast.innerHTML = `
                    <div class="flex items-start gap-3">
                        <div class="flex-1">
                            <p class="font-semibold">${title}</p>
                            <p class="text-sm mt-1">${message}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                `;
                
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.style.transition = 'opacity 0.5s';
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 500);
                }, 5000);
            }
        });

        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('.animate-slideIn');
            flashMessages.forEach(message => {
                setTimeout(() => {
                    message.style.transition = 'opacity 0.5s ease-out';
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 500);
                }, 5000);
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>