import { build, context } from 'esbuild';
import { readFileSync } from 'node:fs';

const javascript = {
  entryPoints: {
    'app.min': 'src/js/app.js',
    'main.min': 'src/js/main.js',
    'admin-settings.min': 'src/js/admin-settings.js',
    'archive.min': 'src/js/archive.js',
    'talk-manager.min': 'src/js/components/talk-manager.js',
  },
  outdir: 'assets/js',
  bundle: true,
  format: 'iife',
  minify: true,
};

const codeHighlight = {
  ...javascript,
  entryPoints: { 'code-highlight.min': 'src/js/components/code-highlight.js' },
  banner: {
    js: '/*!\n' + readFileSync(new URL('../node_modules/highlight.js/LICENSE', import.meta.url), 'utf8').trim() + '\n*/',
  },
};

const stylesheet = {
  entryPoints: ['src/css/main.css', 'src/css/admin-settings.css', 'src/css/wide-dock.css', 'src/css/wide-rails.css'],
  outdir: 'assets/css',
  bundle: true,
  external: ['../images/*', '../fonts/*'],
};

const builds = [javascript, codeHighlight, stylesheet];

if (process.argv.includes('--watch')) {
  const watchers = await Promise.all(builds.map((options) => context(options)));
  await Promise.all(watchers.map((watcher) => watcher.watch()));
} else {
  await Promise.all(builds.map((options) => build(options)));
}
