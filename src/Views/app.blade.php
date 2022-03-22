<!DOCTYPE html>
<html class="h-full">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet" />
    <script src="{{ mix('/js/app.js') }}" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
  </head>
  <body class="h-full scrollbar">
    <div id="app" class="min-h-full flex"></div>
  </body>
</html>