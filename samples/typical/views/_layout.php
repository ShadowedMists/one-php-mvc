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
        <link rel="stylesheet" type="text/css" href="<?php echo $this->content_url("/css/bootstrap.min.css"); ?>" />
        <?php $this->render_styles(); ?>
        <script type="text/javascript" src="<?php echo $this->content_url("/js/bootstrap.min.js"); ?>" ></script>
        <?php $this->render_scripts(); ?>
    </head>
    <body>
        <header class="container" style="margin-top: 1rem">
            <nav class="navbar navbar-light bg-faded">
                <ul class="nav navbar-nav">
                    <li class="nav-item"><a href="<?php echo $this->route_url('index', 'home'); ?>" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="<?php echo $this->route_url('index', 'about'); ?>" class="nav-link">About</a></li>
                </ul>
            </nav>
        </header>
        
        <div class="container" style="margin-top: 1rem">
            <?php $this->render_body(); ?>
        </div>
            
        <footer class="container">
            <p>&copy; 2013 - <?php echo date('Y'); ?> one-php-mvc</p>
        </footer>
    </body>
</html>
