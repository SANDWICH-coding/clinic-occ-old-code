<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Health Records System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <img src="https://www.sis.occph.com/build/assets/OCC_LOGO-BWCM4zrL.png" alt="OCC Logo" class="h-12 mr-3">
                <span class="text-xl font-bold text-blue-700">OCC Health Services</span>
            </div>
            <div>
                @guest
                    <a href="{{ route('login') }}" class="text-blue-700 hover:text-blue-900 font-medium mr-4">Login</a>
                    <a href="{{ route('register') }}" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg font-medium transition duration-200">Register</a>
                @endguest
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-blue-700 text-white py-16 rounded-b-lg shadow-lg mb-12">
        <div class="container mx-auto px-4 text-center">
            <!-- Logo above the title -->
            <div class="flex justify-center mb-6">
                <img src="https://www.sis.occph.com/build/assets/OCC_LOGO-BWCM4zrL.png" alt="OCC Logo" class="h-28 p-2 rounded-lg">
            </div>
            
            <h1 class="text-4xl font-bold mb-4">Digital Health Records and Services Management System</h1>
            <p class="text-xl mb-8">Healthcare management for Opol Community College</p>
            <div class="flex justify-center space-x-4">
                @guest
                    <a href="{{ route('register') }}" class="bg-green-600 hover:bg-green-700 px-6 py-3 rounded-lg font-medium transition duration-200">Student Registration</a>
                    <a href="{{ route('login') }}" class="bg-white text-blue-700 hover:bg-gray-100 px-6 py-3 rounded-lg font-medium transition duration-200">Login</a>
                @endguest
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="mb-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Our Services</h2>
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-3 gap-6">
                <!-- Feature 1 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-200">
                    <div class="text-blue-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-800">Digital Records</h3>
                    <p class="text-gray-600">Secure and accessible health records for all students and staff.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-200">
                    <div class="text-blue-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-800">Appointment Scheduling</h3>
                    <p class="text-gray-600">Easy online appointment booking with our healthcare professionals.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-200">
                    <div class="text-blue-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-800">Health Monitoring</h3>
                    <p class="text-gray-600">Track and monitor your health status throughout your academic journey.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- System Overview -->
    <section class="bg-gray-100 p-8 rounded-lg mb-12">
        <div class="container mx-auto">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">About Our System</h2>
            <p class="text-gray-600 mb-4">
                The Digital Health Records and Services Management System provides comprehensive healthcare solutions for students and staff of Opol Community College. 
                Our platform ensures secure, efficient, and accessible health services for the entire college community.
            </p>
            <div class="grid md:grid-cols-2 gap-6 mt-6">
                <div>
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">For Students</h3>
                    <ul class="list-disc pl-5 text-gray-600 space-y-1">
                        <li>Easy online registration</li>
                        <li>Access to health records</li>
                        <li>Appointment scheduling</li>
                        <li>Health alerts and reminders</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-2 text-gray-800">For Staff</h3>
                    <ul class="list-disc pl-5 text-gray-600 space-y-1">
                        <li>Comprehensive patient management</li>
                        <li>Secure record keeping</li>
                        <li>Health statistics and reporting</li>
                        <li>Efficient clinic operations</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center mb-4 md:mb-0">
                    <img src="https://www.sis.occph.com/build/assets/OCC_LOGO-BWCM4zrL.png" alt="OCC Logo" class="h-10 mr-3">
                    <div>
                        <h3 class="text-lg font-bold">Opol Community College</h3>
                        <p class="text-gray-400 text-sm">Health Services Department</p>
                    </div>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-gray-400">&copy; 2023 Opol Community College. All rights reserved.</p>
                    <p class="text-gray-400 text-sm">Digital Health Records and Services Management System</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>