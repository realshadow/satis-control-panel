<?php
    /**
     * @var string $nodeServer
     * @var string $webpackDevServer
     * @var string $satis
     */
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Satis Control Panel</title>

        <link rel="stylesheet" href="{{ asset('css/app.css') }}" type="text/css" media="screen" />

        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap.min.js') }}"></script>
        
        <script src="{!! $nodeServer !!}/socket.io/socket.io.js"></script>

        <script type="text/javascript">
            var Satis = {!! $satis !!};
        </script>
    </head>
    <body>
        <div class="container"></div>

        @if (app()->isLocal())
            <script src="{!! $webpackDevServer !!}/webpack-dev-server.js"></script>
            <script src="{!! $webpackDevServer !!}/bundle.js"></script>
        @else
            <script src="{{ asset('js/bundle.js') }}"></script>
        @endif
    </body>
</html>
