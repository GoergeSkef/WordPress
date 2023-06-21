
<html>
<head>
<title>Tutorial theme</title>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri().'/js/jquery.js'; ?>">
</script>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri().'/js/jquery-ui.min.js'; ?>">
</script>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri().'/js/bootstrap.js'; ?>">
</script>
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri().'/css/bootstrap.css'; ?>">
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>">
</head>

<body>

<a class="title" href="<?php echo get_home_url(); ?>"> <h1><?php echo get_bloginfo( 'name' ); ?></h1></a>

<div id="navmenu">
    <ul>
        <li>
	        <?php wp_nav_menu( array( 'theme_location' => 'header-nav', 'container_class' => 'header_menu' ) ); ?>
        </li>
    </ul>
</div>
<div>
    <ul style=" padding:0; list-style-type: none">
        <?php dynamic_sidebar( 'Project sidebar' ); ?>
    </ul>
</div>