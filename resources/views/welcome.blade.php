<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'PocketLedger') }}</title>
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
</head>
<body>
    <main>
        <h1>PocketLedger</h1>
        <p>Personal finance and budget tracker</p>
    </main>
</body>
</html>
