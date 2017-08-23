<!DOCTYPE html>
<html>
<head>
	<meta name="robots" content="index,follow,noodp,noydir" />
	<meta charset="utf-8">
    <!--[if IE]><meta http-equiv='X-UA-Compatible' content='IE=edge'><![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>+Media | @yield('title')</title>
	<link rel="stylesheet" type="text/css" href="/css/bootstrap-4.0.0/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="/css/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="/css/style.css">
	<script type="text/javascript" src="/js/jquery-3.2.1.min.js"></script>
	<script type="text/javascript" src="/js/jquery-migrate-1.4.1.min.js"></script>
</head>
<body>
	<header>
		<div class="container">
			<div class="logo"><a href="/"><img src="/images/media-logo.png" width="246" height="75" alt="+Media" /></a></div>
		</div>
	</header>
	<div id="content-area">
		@yield('content')
	</div>
	<footer>
		<div class="text-center">Applied by Randy</div>
	</footer>

	<script type="text/javascript" src="/css/bootstrap-4.0.0/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="/js/common.js"></script>
</body>
</html>