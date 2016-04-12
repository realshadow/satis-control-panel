var app = require('express')();
var fs = require('fs');
var jf = require('jsonfile');
var config = jf.readFileSync(__dirname + '/config.json');
var lockFile = __dirname + '/../' + config['lock_file'];

// Initialize server
var server;
if (/^https/.test(config.host)) {
    if (config.SSLCertificateKeyFile === '' || config.SSLCertificateFile === '') {
        throw 'If you want to use HTTPS server, path to SSL certificates must be provided.';
    }

    server = require('https').Server({
      key: fs.readFileSync(config.SSLCertificateKeyFile),
      cert: fs.readFileSync(config.SSLCertificateFile)
    }, app);
} else {
     server = require('http').Server(app);
}

server.listen(config.port, function() {
    console.log('Listening on *:' + config.port);
});

// Initialize socket
var io = require('socket.io')(server);
io.sockets.on('connection', function(socket) {
	fs.watchFile(lockFile, function(event, fileName) {
		jf.readFile(lockFile, function(err, data) {
			socket.volatile.emit('notification', data);
		});
	});
});

// Initialize routing
app.get('/', function(req, res) {
	res.sendFile(__dirname + '/index.html');
});
