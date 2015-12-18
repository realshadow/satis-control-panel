var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var jf = require('jsonfile');
var fs = require('fs');
var config = jf.readFileSync(__dirname + '/config.json');

var lockFile = __dirname + '/../' + config['lock_file'];

io.sockets.on('connection', function(socket) {
	fs.watchFile(lockFile, function(event, fileName) {
		jf.readFile(lockFile, function(err, data) {
			socket.volatile.emit('notification', data);
		});
	});
});

app.get('/', function(req, res) {
	res.sendFile(__dirname + '/index.html');
});

http.listen(config.port, function() {
	console.log('Listening on *:' + config.port);
});