<!DOCTYPE html>
<html lang="<?php echo $this->request->lang; ?>">
    <head>
        <title><?php echo $this->meta->title; ?></title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, user-scalable=no">
        <meta name="description" content="<?php echo $this->meta->description; ?>">
        <meta name="keywords" content="<?php echo $this->meta->keywords; ?>">
        <meta name="author" content="<?php echo $this->meta->author; ?>">
        <link rel="image_src" href="<?php echo $this->meta->image; ?>"/>
        <meta property="og:title" content="<?php echo $this->meta->title; ?>" />
        <meta property="og:image" content="<?php echo $this->meta->image; ?>" />
        <meta name="twitter:title" content="<?php echo $this->meta->title; ?>">
        <meta name="twitter:image" content="<?php echo $this->meta->image; ?>">
        <link rel="stylesheet" type="text/css" href="/css/site.css" />
        <?php $this->render_styles(); ?>
        <?php $this->render_scripts(); ?>
    </head>
    <body>
        <header>
            <nav>
                <ul class="menu">
                    <li><a href="<?php echo $this->route_url('index', 'home'); ?>">Home</a></li>
                    <li><a href="<?php echo $this->route_url('index', 'about'); ?>">About</a></li>
                </ul>
            </nav>
        </header>

        <?php $this->render_body(); ?>

        <footer>
            &copy; 2013 - <?php echo date('Y'); ?> one-php-mvc
        </footer>
    </body>
</html>
