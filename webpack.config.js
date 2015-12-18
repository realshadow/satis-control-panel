var glob = require('glob');
var path = require('path');

module.exports = {
	entry: ['webpack/hot/dev-server', './resources/assets/typescript/app.tsx']
		.concat(glob.sync('./resources/assets/typescript/flux/**/*.tsx'))
		.concat(glob.sync('./resources/assets/typescript/helpers/**/*.ts')),
	output: {
		filename: 'bundle.js'
	},
	resolve: {
		extensions: ['', '.webpack.js', '.web.js', '.tsx', '.ts', '.js', '.jsx']
	},
	module: {
		loaders: [
			{ test: /\.ts(x?)$/, loader: 'react-hot!ts-loader' },
			{ test: /\.less$/, loader: "style!css!less" }
		]
	}
}
