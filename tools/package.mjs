import { execFileSync } from 'node:child_process';
import { cpSync, existsSync, mkdirSync, mkdtempSync, readdirSync, renameSync, rmSync } from 'node:fs';
import { basename, dirname, join } from 'node:path';

const outputDirectory = 'dist';
mkdirSync(outputDirectory, { recursive: true });
const stagingDirectory = mkdtempSync(join(outputDirectory, '.glaze-'));
const themeDirectory = join(stagingDirectory, 'glaze');

try {
  mkdirSync(themeDirectory);

  const templates = readdirSync('.', { withFileTypes: true })
    .filter((entry) => entry.isFile() && entry.name.endsWith('.php'))
    .map((entry) => entry.name);

  const required = [
    'style.css',
    ...templates,
    'assets/js/app.min.js',
    'assets/js/main.min.js',
    'assets/js/admin-settings.min.js',
    'assets/js/archive.min.js',
    'assets/js/talk-manager.min.js',
    'assets/js/code-highlight.min.js',
    'assets/css/main.css',
    'assets/css/admin-settings.css',
    'assets/css/wide-dock.css',
    'assets/css/wide-rails.css',
    'assets/css/wordpress',
    'assets/icons',
    'assets/images',
    'assets/fonts',
  ];
  const optional = ['inc', 'template-parts', 'languages', 'assets/demo', 'THIRD_PARTY_NOTICES.md', 'theme.json', 'screenshot.png'];

  for (const entry of [...required, ...optional.filter((path) => existsSync(path))]) {
    const destination = join(themeDirectory, entry);
    mkdirSync(dirname(destination), { recursive: true });
    cpSync(entry, destination, {
      recursive: true,
      filter: (source) => !basename(source).startsWith('.') && !source.endsWith('.po'),
    });
  }

  execFileSync('zip', ['-q', '-r', 'glaze.zip', 'glaze'], { cwd: stagingDirectory });
  const archive = join(outputDirectory, 'glaze.zip');
  renameSync(join(stagingDirectory, 'glaze.zip'), archive);
  console.log(archive);
} finally {
  rmSync(stagingDirectory, { recursive: true, force: true });
}
