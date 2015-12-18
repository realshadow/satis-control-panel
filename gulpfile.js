var gulp = require('gulp');
var less = require('gulp-less');
var typescript = require('gulp-typescript');
var watch = require('gulp-watch');
var concat = require('gulp-concat');
var livereload = require('gulp-livereload');
var sourcemaps = require('gulp-sourcemaps');
var tsProject = typescript.createProject('./tsconfig.json', { sortOutput: true });

var paths = {
    'less': {
        'input':  './resources/assets/less',
        'output': './public/control-panel/css'
    },
    'ts': {
        'input': './resources/assets/typescript',
        'output': './public/control-panel/js',
        'output_file': 'bundle.js'
    }
};

gulp.task('less', function() {
    return gulp.src(paths.less.input + '/app.less')
        .pipe(sourcemaps.init())
        .pipe(less())
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(paths.less.output))
        .pipe(livereload());
});

/*
gulp.task('ts', function() {
    return tsProject.src()
        .pipe(sourcemaps.init())
        .pipe(typescript(tsProject))
        .js
        .pipe(concat('bundle.js'))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(paths.ts.output));
});
*/

gulp.task('watch', function() {
    gulp.watch([paths.less.input + '/*.less'], ['less']);
    //gulp.watch([paths.ts.input + '/*.ts'], ['ts']);
});

gulp.task('default', ['watch']);
gulp.task('init', ['less']);