var gulp = require("gulp");

gulp.task('dist', function (done) {
	const files = gulp.src(
	  [
		'./**/*.php',
		'./**/*.txt',
		'./**/*.css',
		'./**/*.scss',
		'./**/*.png',
		'./inc/**',
		'./assets/**',
		'./icons/**',
		'./languages/**',
		"./vendor/**",
		"!./.vscode/**",
		"!./bin/**",
		"!./dist/**",
		"!./node_modules/**/*.*",
		"!./tests/**",
	  ], {
		base: './'
	  }
	)
	files.pipe(gulp.dest("dist/bill-vektor-attendance"));
	done();
  });
