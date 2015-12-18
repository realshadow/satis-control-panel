<?php
    /**
     * @var string $nodeServer
     * @var string $satis
     */
?>

<!DOCTYPE html>
<html>
    <head>
        <title>CFH Composer Repository</title>

        <link rel="stylesheet" href="{{ asset('css/app.css') }}" type="text/css" media="screen" />

        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap.min.js') }}"></script>
        
        <script src="<?= $nodeServer ?>/socket.io/socket.io.js"></script>

        <script type="text/javascript">
            var Satis = {!! $satis !!};
        </script>
    </head>
    <body>
        <div class="container"></div>

        @if (env('APP_ENV', 'local') === 'local')
            <script src="http://192.168.56.102:9001/webpack-dev-server.js"></script>
            <script src="http://192.168.56.102:9001/bundle.js"></script>
        @else
            <script src="{{ asset('js/bundle.js') }}"></script>
        @endif
    </body>
</html>
