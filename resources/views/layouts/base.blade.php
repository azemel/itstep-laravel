<html>
<head>

  <title>@yield('title', 'App')</title>

  <link href="/style.css" type="text/css" rel="stylesheet" />

</head>

<body>

  <x-navigation :active="$navigationItem ?? null"/>

  @yield('body')

</body>
</html>