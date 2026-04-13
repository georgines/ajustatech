import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import html from '@rollup/plugin-html';
import dotenv from 'dotenv';
dotenv.config();

// Processing Window Assignment for Libs like jKanban, pdfMake
function libsWindowAssignment() {
  return {
    name: 'libsWindowAssignment',

    transform(src, id) {
      if (id.includes('jkanban.js')) {
        return src.replace('this.jKanban', 'window.jKanban');
      } else if (id.includes('vfs_fonts')) {
        return src.replaceAll('this.pdfMake', 'window.pdfMake');
      }
    }
  };
}

function getHostFromUrl(url) {
    return url.replace(/^https?:\/\//, '').split('/')[0];
  }

  const appUrl = process.env.APP_URL || 'http://localhost';
  const host = getHostFromUrl(appUrl);

export default defineConfig({
    server: {
        host: host,
        port: 3000,
      },
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/assets/css/demo.css',
        'resources/js/app.js',
          'resources/assets/js/config.js',
          'resources/assets/js/front-config.js',
          'resources/assets/js/front-main.js',
          'resources/assets/vendor/js/helpers.js',
          'resources/assets/vendor/js/template-customizer.js',
          'resources/assets/vendor/js/dropdown-hover.js',
          'resources/assets/vendor/js/mega-dropdown.js',
          'resources/assets/vendor/js/bootstrap.js',
          'resources/assets/vendor/js/menu.js',
          'resources/assets/vendor/libs/popper/popper.js',
          'resources/assets/vendor/libs/node-waves/node-waves.js',
          'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
          'resources/assets/vendor/libs/hammer/hammer.js',
          'resources/assets/vendor/libs/typeahead-js/typeahead.js',
          'resources/assets/js/bundles/dashboard-analytics.scss',
          'resources/assets/js/bundles/dashboard-analytics.js'
      ],
      refresh: [
        'modules/Ajustatech/**/src/Views/**/*.blade.php',
        'modules/Ajustatech/**/src/Livewire/*.php'
      ]
    }),
    html(),
    libsWindowAssignment()
  ]
});
