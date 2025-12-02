<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

    <div class="flex">

        <!-- Sidebar -->
        <aside class="w-64 h-screen bg-white shadow-lg p-5 hidden md:block">
            <h1 class="text-2xl font-bold mb-8">My Dashboard</h1>

            <ul class="space-y-3 text-gray-700">
                <li>
                    <a href="/dashboard" class="block p-2 rounded hover:bg-gray-200">Dashboard</a>
                </li>
                <li>
                    <a href="#" class="block p-2 rounded hover:bg-gray-200">Users</a>
                </li>
                <li>
                    <a href="#" class="block p-2 rounded hover:bg-gray-200">Products</a>
                </li>
                <li>
                    <a href="#" class="block p-2 rounded hover:bg-gray-200">Settings</a>
                </li>
            </ul>
        </aside>

        <!-- Main content -->
        <div class="flex-1">

            <!-- Navbar -->
            <nav class="bg-white shadow p-4 flex justify-between">
                <h2 class="text-xl font-semibold">@yield('title')</h2>
                <div>
                    <span class="text-gray-600">Hello, Admin</span>
                </div>
            </nav>

            <!-- Page content -->
            <main class="p-6">
                @yield('content')
            </main>

        </div>
    </div>

</body>
</html>
