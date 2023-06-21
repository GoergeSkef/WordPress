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
get_template_part('partials/content');
?>
<footer>
	<?php get_footer(); ?>
</footer>
</body>


</html>