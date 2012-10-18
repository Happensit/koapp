<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?php echo $title ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="description" content="<?php echo $description ?>" />
  <meta name="keywords" content="<?php echo $keywords ?>" />
<?php foreach($css as $css_file) echo '  '.HTML::style($css_file), "\n" ?>
<?php foreach($js as $js_file) echo '  '.HTML::script($js_file), "\n" ?>
<?php if ($script != '') echo $script."\n" ?>
</head>
<body>
  <div id="content">
    <?php echo $content ?>
  </div>
  <div id="profiler">
    <?php if (Kohana::$environment !== Kohana::PRODUCTION): ?>
    <?php echo View::factory('profiler/stats'); ?>
    <?php endif ?>
  </div>
</body>
</html>