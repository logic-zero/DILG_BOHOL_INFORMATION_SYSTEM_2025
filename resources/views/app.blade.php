<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>DILG Bohol Province</title>

    @if(isset($page['props']['meta']))
        <meta property="og:title" content="{{ $page['props']['meta']['title'] }}" />
        <meta property="og:description" content="{{ $page['props']['meta']['description'] }}" />
        <meta property="og:image" content="{{ $page['props']['meta']['image'] }}" />
    @else
        <title>{{ config('app.name', 'Laravel') }}</title>
        <meta property="og:title" content="Your Website Title" />
        <meta property="og:description" content="Your description" />
        <meta property="og:image" content="https://images.pexels.com/photos/556416/pexels-photo-556416.jpeg?cs=srgb&dl=landscape-mountains-nature-556416.jpg&fm=jpg" />
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
