/**
 * A simple Gulp 4 Starter Kit for modern web development.
 *
 * @package @jr-cologne/create-gulp-starter-kit
 * @author JR Cologne <kontakt@jr-cologne.de>
 * @copyright 2018 JR Cologne
 * @license https://github.com/jr-cologne/gulp-starter-kit/blob/master/LICENSE MIT
 * @version v0.1.2-alpha
 * @link https://github.com/jr-cologne/gulp-starter-kit GitHub Repository
 * @link https://www.npmjs.com/package/@jr-cologne/create-gulp-starter-kit npm package site
 *
 * ________________________________________________________________________________
 *
 * gulpfile.js
 *
 * The gulp configuration file.
 *
 * Modified for use in login-script.
 *
 */

const gulp                        = require('gulp'),
      del                         = require('del'),
      sourcemaps                  = require('gulp-sourcemaps'),
      plumber                     = require('gulp-plumber'),
      sass                        = require('gulp-sass'),
      autoprefixer                = require('gulp-autoprefixer'),
      cssnano                     = require('gulp-cssnano'),
      babel                       = require('gulp-babel'),
      uglify                      = require('gulp-uglify'),
      concat                      = require('gulp-concat'),

      src_folder                  = './src/',
      src_assets_folder           = src_folder + 'assets/',
      dist_folder                 = './dist/',
      dist_assets_folder          = dist_folder + 'assets/',
      node_modules_folder         = './node_modules/',
      dist_node_modules_folder    = dist_folder + 'node_modules/',
      composer_vendor_folder      = './vendor/',
      dist_composer_vendor_folder = dist_folder + 'vendor/',

      node_dependencies           = [
        'bootstrap',
        'jquery',
        'popper.js'
      ];

gulp.task('clear', () => del([ dist_folder ]));

gulp.task('php', () => {
  return gulp.src([ src_folder + '**/*.php' ], { base: src_folder })
    .pipe(gulp.dest(dist_folder));
});

gulp.task('sass', () => {
  return gulp.src([ src_assets_folder + 'sass/**/*.sass' ])
    .pipe(sourcemaps.init())
      .pipe(plumber())
      .pipe(sass())
      .pipe(autoprefixer({
        browsers: [ 'last 3 versions', '> 0.5%' ]
      }))
      .pipe(cssnano())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(dist_assets_folder + 'css'));
});

gulp.task('js', () => {
  return gulp.src([ src_assets_folder + 'js/**/*.js' ])
    .pipe(sourcemaps.init())
      .pipe(plumber())
      .pipe(babel({
        presets: [ 'env' ]
      }))
      .pipe(concat('all.js'))
      .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(dist_assets_folder + 'js'));
});

gulp.task('vendor_js', () => {
  if (node_dependencies.length === 0) {
    return new Promise((resolve) => {
      console.log("No dependencies specified");
      resolve();
    });
  }

  return gulp.src(node_dependencies.map(dependency => node_modules_folder + dependency + '/**/*.*'), { base: node_modules_folder })
    .pipe(gulp.dest(dist_node_modules_folder));
});

gulp.task('vendor_php', () => {
  return gulp.src([ composer_vendor_folder + '**/*' ], { base: composer_vendor_folder })
    .pipe(gulp.dest(dist_composer_vendor_folder));
});

gulp.task('vendor', gulp.parallel('vendor_js', 'vendor_php'));

gulp.task('build', gulp.series('clear', 'php', 'sass', 'js', 'vendor'));

gulp.task('watch', () => {
  let watch = [
    src_folder + '**/*.php',
    src_assets_folder + 'sass/**/*.sass',
    src_assets_folder + 'js/**/*.js'
  ];

  node_dependencies.forEach(dependency => {
    watch.push(node_modules_folder + dependency + '/**/*.*');
  });

  gulp.watch(watch, gulp.series('build'));
});

gulp.task('default', gulp.series('build', 'watch'));
