<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') OCC CLINIC MANAGEMENT SYSTEM</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .sidebar {
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            width: 256px;
            z-index: 50;
            transform: translate3d(-100%, 0, 0);
            transition: transform 0.3s ease-in-out;
            will-change: transform;
        }
        .sidebar-open {
            transform: translate3d(0, 0, 0);
        }
        .main-content {
            margin-left: 0;
            width: 100%;
            transition: none; /* Remove margin transition to avoid layout shifts */
        }
        .sidebar-collapsed {
            width: 80px !important;
        }
        .sidebar-collapsed .nav-text, .sidebar-collapsed .logo-text, .sidebar-collapsed .user-welcome {
            display: none;
        }
        
        /* Active link styling */
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            color: white;
            font-weight: 600;
            transition: all 0.2s;
            margin-bottom: 0.5rem;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
        }
        
        .nav-link i {
            margin-right: 0.75rem;
            width: 1.5rem;
            text-align: center;
        }
        
        @media (min-width: 768px) {
            .sidebar {
                transform: translate3d(0, 0, 0); /* Sidebar visible on desktop */
            }
            .main-content {
                margin-left: 256px;
                width: calc(100% - 256px);
            }
        }
        
        @media (max-width: 767px) {
            .mobile-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease-in-out 0.1s, visibility 0.3s ease-in-out 0.1s;
                will-change: opacity;
            }
            .mobile-overlay-open {
                opacity: 1;
                visibility: visible;
            }
            .mobile-menu-btn {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 60;
            }
            
            .nav-link span {
                display: none;
            }
            
            .nav-link i {
                margin-right: 0;
            }
            
            .sidebar-open .nav-link span {
                display: inline;
            }
        }

        /* Notification styles */
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 400px;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">
    <!-- Mobile menu button -->
    <button id="mobile-menu-btn" class="mobile-menu-btn bg-blue-900 text-white p-3 rounded-full md:hidden focus:outline-none focus:ring-2 focus:ring-white">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Mobile overlay -->
    <div id="mobile-overlay" class="mobile-overlay"></div>

    <!-- Notification Container -->
    <div id="notification-container" class="notification-toast"></div>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="sidebar bg-gradient-to-b from-blue-900 to-indigo-800 text-white flex flex-col h-screen">
            <!-- Logo -->
            <div class="p-6 flex flex-col items-center justify-center border-b border-indigo-800">
                <a href="{{ route('nurse.dashboard') }}" class="flex flex-col items-center justify-center w-full">
                    <img src="https://scontent.fmnl13-4.fna.fbcdn.net/v/t39.30808-6/510989554_1291512086313864_1697600367017083003_n.jpg?_nc_cat=107&ccb=1-7&_nc_sid=6ee11a&_nc_ohc=ufmPlgigja8Q7kNvwFvg5_p&_nc_oc=AdkVGda15Irn9ds6nkgslNmKCetlzlny0VdM-oCiXljsPuA_WwSVFdsUHElwWJ4z75CCUtqqr17hUsVOILYkX4js&_nc_zt=23&_nc_ht=scontent.fmnl13-4.fna&_nc_gid=IyES0_5ro_a-G226jK3uEw&oh=00_Afc92WGc1iQFxcshYjNaH9_qKTAmLIotAMwGl8u67osuLQ&oe=68F397E7" 
                         alt="OPOL Community College Clinic System Logo" 
                         class="h-20 w-20 rounded-lg object-cover mb-2">
                    <span class="logo-text text-sm font-semibold text-center">OCC clinic Management System</span>
                </a>
            </div>

            <!-- Nav Links -->
            <nav class="flex-1 p-6">
                <a href="{{ route('nurse.dashboard') }}" class="nav-link {{ request()->routeIs('nurse.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="{{ route('nurse.appointments.index') }}" class="nav-link {{ request()->routeIs('nurse.appointments.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check"></i>
                    <span class="nav-text">Appointments</span>
                </a>
                <a href="{{ route('nurse.consultations.index') }}" class="nav-link {{ request()->routeIs('nurse.consultations.*') ? 'active' : '' }}">
                    <i class="fas fa-stethoscope"></i>
                    <span class="nav-text">Consultations</span>
                </a>
                <a href="{{ route('nurse.medical-records.index') }}" class="nav-link {{ request()->routeIs('nurse.medical-records.*') ? 'active' : '' }}">
                    <i class="fas fa-file-medical"></i>
                    <span class="nav-text">Medical Records</span>
                </a>
                <a href="{{ route('nurse.medical-data.index') }}" class="nav-link {{ request()->routeIs('nurse.medical-data.*') ? 'active' : '' }}">
                    <i class="fas fa-database"></i>
                    <span class="nav-text">Medical Data</span>
                </a>
                <a href="{{ route('nurse.symptom-logs.index') }}" class="nav-link {{ request()->routeIs('nurse.symptom-logs.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="nav-text">Symptom Logs</span>
                </a>
                <a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                    <i class="fas fa-comment"></i>
                    <span class="nav-text">Chat</span>
                    <span id="nav-unread-badge" class="hidden ml-auto px-2 py-0.5 bg-red-500 text-white text-xs rounded-full">0</span>
                </a>
                <!-- Student Reports Link -->
               <a href="{{ route('nurse.student-reports.index') }}" 
   class="nav-link {{ request()->routeIs('nurse.student-reports.*') ? 'active' : '' }}">
    <i class="fas fa-file-medical"></i>
    <span class="nav-text">Reports</span>
</a>
            </nav>

            <!-- User + Logout -->
            <div class="p-6 border-t border-indigo-700">
                @auth
                    <div class="user-welcome mb-2 text-sm text-indigo-200 text-center font-medium">Welcome, Nurse</div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm font-semibold text-white transition flex items-center justify-center">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            <span class="nav-text">Logout</span>
                        </button>
                    </form>
                @endauth
            </div>
        </aside>
        <!-- Main Content -->
        <main class="main-content flex-1 p-6">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error') || $errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <div>
                            {{ session('error') }}
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-4 flex items-center" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        {{ session('warning') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Real-time Chat Script -->
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.sidebar');
        const mobileMenuBtn = document.querySelector('#mobile-menu-btn');
        const mobileOverlay = document.querySelector('#mobile-overlay');

        // Toggle sidebar on mobile
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                sidebar.classList.toggle('sidebar-open');
                mobileOverlay.classList.toggle('mobile-overlay-open');
            });
        }
        
        // Close sidebar when clicking on overlay
        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', function(event) {
                event.preventDefault();
                sidebar.classList.remove('sidebar-open');
                mobileOverlay.classList.remove('mobile-overlay-open');
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 767 && 
                !sidebar.contains(event.target) && 
                !mobileMenuBtn.contains(event.target) && 
                sidebar.classList.contains('sidebar-open')) {
                sidebar.classList.remove('sidebar-open');
                mobileOverlay.classList.remove('mobile-overlay-open');
            }
        });

        // Highlight active link on click
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // ========== REAL-TIME CHAT IMPLEMENTATION ==========
        const currentUserId = {{ auth()->id() }};
        
        // Initialize Pusher for real-time chat
        function initializeRealTimeChat() {
            try {
                // Enable pusher logging - remove in production
                Pusher.logToConsole = true;

                const pusher = new Pusher('a433f36a50922340a863', {
                    cluster: 'ap1',
                    encrypted: true
                });

                console.log('🔧 Initializing Pusher for nurse...');

                // Test connection
                pusher.connection.bind('connected', () => {
                    console.log('✅ Pusher connected successfully for nurse!');
                    console.log('Socket ID:', pusher.connection.socket_id);
                });

                pusher.connection.bind('error', (err) => {
                    console.error('❌ Pusher connection error:', err);
                });

                // Listen for new messages on nurse's personal channel
                const channel = pusher.subscribe('chat.user.' + currentUserId);
                
                channel.bind('new.chat.message', function(data) {
                    console.log('💬 New message received by nurse:', data);
                    
                    // Update unread badge
                    updateUnreadBadge(data.unread_count);
                    
                    // If viewing the conversation, add message instantly
                    if (isViewingConversation(data.message.conversation_id)) {
                        addMessageToChat(data.message);
                    } else {
                        // Show notification if not viewing the conversation
                        showNewMessageNotification(data.message);
                    }
                });

                // Handle subscription success
                channel.bind('pusher:subscription_succeeded', () => {
                    console.log('✅ Successfully subscribed to chat channel for nurse');
                });

                // Handle subscription errors
                channel.bind('pusher:subscription_error', (status) => {
                    console.error('❌ Pusher subscription error:', status);
                });

            } catch (error) {
                console.error('❌ Pusher initialization failed:', error);
            }
        }

        // Update unread badge count
        function updateUnreadBadge(count) {
            const badge = document.getElementById('nav-unread-badge');
            if (badge) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.toggle('hidden', count <= 0);
                
                // Update document title with unread count
                updateDocumentTitle(count);
            }
        }

        // Update document title with unread count
        function updateDocumentTitle(unreadCount) {
            const baseTitle = 'OCC CLINIC MANAGEMENT SYSTEM';
            if (unreadCount > 0) {
                document.title = `(${unreadCount}) ${baseTitle}`;
            } else {
                document.title = baseTitle;
            }
        }

        // Check if user is viewing the conversation
        function isViewingConversation(conversationId) {
            const currentPath = window.location.pathname;
            return currentPath.includes('/chat/conversation/' + conversationId);
        }

        // Add message to chat UI
        function addMessageToChat(message) {
            const container = document.getElementById('messages-container');
            if (!container) return;

            // Create message element
            const messageEl = document.createElement('div');
            messageEl.className = `flex justify-start mb-4 message-item`;
            messageEl.innerHTML = `
                <div class="max-w-xs lg:max-w-md">
                    <div class="bg-gray-200 text-gray-800 rounded-lg px-4 py-2">
                        <p class="text-sm font-medium text-gray-700">${message.sender_name}</p>
                        <p class="text-sm mt-1">${escapeHtml(message.content)}</p>
                        <p class="text-xs mt-1 opacity-70 text-right">${message.sent_at}</p>
                    </div>
                </div>
            `;

            // Add with animation
            container.appendChild(messageEl);
            scrollToBottom();
            
            // Add animation
            setTimeout(() => {
                messageEl.style.opacity = '1';
                messageEl.style.transform = 'translateY(0)';
            }, 10);

            // Update unread count after adding message
            fetchUnreadCount();
        }

        // Scroll to bottom of messages container
        function scrollToBottom() {
            const container = document.getElementById('messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }

        // Show notification for new message
        function showNewMessageNotification(message) {
            // Create in-app notification
            const notification = createNotification(
                `💬 New message from ${message.sender_name}`,
                message.content,
                'fas fa-comment text-blue-500',
                'border-blue-500'
            );
            
            showNotification(notification);
            playNotificationSound();

            // Also show browser notification if permitted
            showBrowserNotification(message);
        }

        // Create notification HTML
        function createNotification(title, message, iconClass, borderClass = 'border-blue-500') {
            const notificationId = 'notification-' + Date.now();
            return `
                <div id="${notificationId}" class="bg-white rounded-lg shadow-lg border-l-4 ${borderClass} p-4 max-w-sm transform transition-all duration-300 translate-x-full opacity-0 mb-2">
                    <div class="flex items-start">
                        <i class="${iconClass} text-xl mr-3 mt-1"></i>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 text-sm">${title}</h4>
                            <p class="text-xs text-gray-600 mt-1 line-clamp-2">${message}</p>
                        </div>
                        <button onclick="closeNotification('${notificationId}')" class="ml-2 text-gray-400 hover:text-gray-600 text-sm">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
        }

        // Show notification
        function showNotification(notificationHtml) {
            const notificationContainer = document.getElementById('notification-container');
            if (!notificationContainer) return;
            
            notificationContainer.innerHTML += notificationHtml;
            
            // Animate in
            setTimeout(() => {
                const notification = document.getElementById(notificationContainer.lastElementChild?.id);
                if (notification) {
                    notification.classList.remove('translate-x-full', 'opacity-0');
                    notification.classList.add('translate-x-0', 'opacity-100');
                }
            }, 10);
            
            // Auto remove after 8 seconds
            setTimeout(() => {
                if (notificationContainer.lastElementChild) {
                    closeNotification(notificationContainer.lastElementChild.id);
                }
            }, 8000);
        }

        // Close notification
        window.closeNotification = function(notificationId) {
            const notification = document.getElementById(notificationId);
            if (notification) {
                notification.classList.remove('translate-x-0', 'opacity-100');
                notification.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => notification.remove(), 300);
            }
        }

        // Show browser notification
        function showBrowserNotification(message) {
            if ("Notification" in window) {
                if (Notification.permission === "granted") {
                    createBrowserNotification(message);
                } else if (Notification.permission !== "denied") {
                    Notification.requestPermission().then(permission => {
                        if (permission === "granted") {
                            createBrowserNotification(message);
                        }
                    });
                }
            }
        }

        // Create browser notification
        function createBrowserNotification(message) {
            const notification = new Notification(`💬 New message from ${message.sender_name}`, {
                body: message.content.length > 100 ? 
                      message.content.substring(0, 100) + '...' : message.content,
                icon: '/favicon.ico',
                tag: 'chat-message'
            });

            notification.onclick = function() {
                window.focus();
                if (message.conversation_id) {
                    window.location.href = `/chat/conversation/${message.conversation_id}`;
                }
                this.close();
            };

            // Auto close after 5 seconds
            setTimeout(() => notification.close(), 5000);
        }

        // Play notification sound
        function playNotificationSound() {
            try {
                // Create a simple notification sound using Web Audio API
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = 800;
                oscillator.type = 'sine';
                
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
                
            } catch (error) {
                console.log('Audio playback not supported');
            }
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Fetch unread count from server
        function fetchUnreadCount() {
            fetch('{{ route("api.chat.unread.count") }}')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        updateUnreadBadge(data.unread_count);
                    }
                })
                .catch(error => {
                    console.log('Could not fetch unread count:', error);
                });
        }

        // Initialize real-time chat
        initializeRealTimeChat();

        // Initial fetch to set badge on page load
        fetchUnreadCount();

        // Periodically check for new messages (fallback)
        setInterval(fetchUnreadCount, 30000); // Every 30 seconds

        // Fallback for connection issues
        function handleConnectionIssues() {
            console.log('🔄 Using polling as fallback for real-time updates');
            setInterval(fetchUnreadCount, 10000); // Check every 10 seconds
        }

        // Check Pusher connection status
        if (typeof Pusher !== 'undefined') {
            // This will be set when Pusher initializes
            setTimeout(() => {
                if (window.pusher && window.pusher.connection.state !== 'connected') {
                    handleConnectionIssues();
                }
            }, 5000);
        }
    });
    </script>
    @stack('scripts')
</body>
</html>