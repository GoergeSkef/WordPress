<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?php bloginfo('name') ?></title>
    <link rel="stylesheet" href="<?php bloginfo('stylesheet_url') ?>">

</head>

<body>
<header>
	<?php
		get_header();
	?>
</header>
<?php 
            $args = array(
                'post_type' => 'post',
                'posts_per_page' => 5,
                'post__not_in' => array($dontshowthisguy)
            );

            $query = new WP_Query($args);

            while($query->have_posts()) : $query->the_post();
        ?>
            
            <p><?php the_title(); ?></p>
            

        <?php endwhile; wp_reset_query();  ?>
    <?php
    get_template_part('partials/postgrid');
    ?>
<footer>
<?php get_footer(); ?>
</footer>
</body>


</html>


