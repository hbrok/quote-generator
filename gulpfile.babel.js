import fs from 'fs';
import path from 'path';

import gulp from 'gulp';

// Load all gulp plugins automatically
// and attach them to the `plugins` object
import plugins from 'gulp-load-plugins';
import uglify from 'gulp-uglify';

// Temporary solution until gulp 4
// https://github.com/gulpjs/gulp/issues/355
import runSequence from 'run-sequence';

import archiver from 'archiver';
import glob from 'glob';
import del from 'del';

import pkg from './package.json';

const dirs = pkg['h5bp-configs'].directories;

// ---------------------------------------------------------------------
// | Helper tasks                                                      |
// ---------------------------------------------------------------------

gulp.task('archive:create_archive_dir', () => {
    fs.mkdirSync(path.resolve(dirs.archive), '0755');
});

gulp.task('archive:zip', (done) => {

    const archiveName = path.resolve(dirs.archive, `${pkg.name}_v${pkg.version}.zip`);
    const zip = archiver('zip');
    const files = glob.sync('**/*.*', {
        'cwd': dirs.dist,
        'dot': true // include hidden files
    });
    const output = fs.createWriteStream(archiveName);

    zip.on('error', (error) => {
        done();
        throw error;
    });

    output.on('close', done);

    files.forEach( (file) => {

        const filePath = path.resolve(dirs.dist, file);

        // `zip.bulk` does not maintain the file
        // permissions, so we need to add files individually
        zip.append(fs.createReadStream(filePath), {
            'name': file,
            'mode': fs.statSync(filePath).mode
        });

    });

    zip.pipe(output);
    zip.finalize();

});

gulp.task('clean', (done) => {
    del([
        dirs.archive,
        dirs.dist
    ]).then( () => {
        done();
    });
});

gulp.task('copy', [
    'copy:.htaccess',
    'copy:license',
    'copy:main.css',
    'copy:main.js',
    'copy:misc'
]);

gulp.task('copy:.htaccess', () =>
    gulp.src('node_modules/apache-server-configs/dist/.htaccess')
        .pipe(plugins().replace(/# ErrorDocument/g, 'ErrorDocument'))
        .pipe(gulp.dest(dirs.dist))
);

gulp.task('copy:license', () =>
    gulp.src('LICENSE.txt')
        .pipe(gulp.dest(dirs.dist))
);

gulp.task('copy:main.css', () => {

    const banner = `/*! HTML5 Boilerplate v${pkg.version} | ${pkg.license} License | ${pkg.homepage} */\n\n`;

    gulp.src(`${dirs.src}/css/main.css`)
        .pipe(plugins().header(banner))
        .pipe(plugins().autoprefixer({
            browsers: ['last 2 versions', 'ie >= 8', '> 1%'],
            cascade: false
        }))
        .pipe(plugins().cssnano())
        .pipe(gulp.dest(`${dirs.dist}/css`));
});

gulp.task('copy:main.js', () => {

    gulp.src(`${dirs.src}/js/main.js`)
        .pipe( uglify()
            .on('error', e => { console.log(e); })
        )
        .pipe(gulp.dest(`${dirs.dist}/js`));
});

gulp.task('copy:misc', () =>
    gulp.src([

        // Copy all files
        `${dirs.src}/**/*`,

        // Exclude the following files
        // (other tasks will handle the copying of these files)
        `!${dirs.src}/css/main.css`,
        `!${dirs.src}/js/main.js`,
        `!${dirs.src}/index.html`

    ], {

        // Include hidden files by default
        dot: true

    }).pipe(gulp.dest(dirs.dist))
);

// ---------------------------------------------------------------------
// | Main tasks                                                        |
// ---------------------------------------------------------------------

gulp.task('archive', (done) => {
    runSequence(
        'build',
        'archive:create_archive_dir',
        'archive:zip',
    done)
});

gulp.task('build', (done) => {
    runSequence(
        'clean',
        'copy',
    done)
});

gulp.task('default', ['build']);
