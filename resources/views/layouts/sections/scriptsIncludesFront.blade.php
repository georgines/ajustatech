@php
$appLocaleRaw = app()->getLocale();
$appLocaleBase = explode('-', $appLocaleRaw)[0];
$templateCustomizerLang = in_array($appLocaleRaw, ['en', 'fr', 'ar', 'de'], true)
  ? $appLocaleRaw
  : (in_array($appLocaleBase, ['en', 'fr', 'ar', 'de'], true) ? $appLocaleBase : 'en');
@endphp

<!-- laravel style -->
@vite(['resources/assets/vendor/js/helpers.js'])
<!-- beautify ignore:start -->
@if ($configData['hasCustomizer'])
  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
  @vite(['resources/assets/vendor/js/template-customizer.js'])
@endif

  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  @vite(['resources/assets/js/front-config.js'])

@if ($configData['hasCustomizer'])
<script type="module">
    window.templateCustomizer = new TemplateCustomizer({
      cssPath: '',
      themesPath: '',
      defaultStyle: "{{$configData['styleOpt']}}",
      displayCustomizer: "{{$configData['displayCustomizer']}}",
      lang: '{{ $templateCustomizerLang }}',
      pathResolver: function(path) {
        var resolvedPaths = {
          // Core stylesheets
          @foreach (['core'] as $name)
            '{{ $name }}.scss': '{{ Vite::asset('resources/assets/vendor/scss'.$configData["rtlSupport"].'/'.$name.'.scss') }}',
            '{{ $name }}-dark.scss': '{{ Vite::asset('resources/assets/vendor/scss'.$configData["rtlSupport"].'/'.$name.'-dark.scss') }}',
          @endforeach

          // Themes
          @foreach (['default', 'bordered', 'semi-dark'] as $name)
            'theme-{{ $name }}.scss': '{{ Vite::asset('resources/assets/vendor/scss'.$configData["rtlSupport"].'/theme-'.$name.'.scss') }}',
            'theme-{{ $name }}-dark.scss': '{{ Vite::asset('resources/assets/vendor/scss'.$configData["rtlSupport"].'/theme-'.$name.'-dark.scss') }}',
          @endforeach
        }
        return resolvedPaths[path] || path;
      },
      'controls': <?php echo json_encode(['rtl', 'style']); ?>,

    });
  </script>
@endif
