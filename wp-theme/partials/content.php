

<div style="float: left; width: 50%">
	<?php
		the_post_thumbnail('single_large');
	?>
</div>

<div style="float: left;">
	<?php
		the_title( '<h2>', '</h2>' );
	?>
	<br>
	<br>
	<?php
	while ( have_posts() ) : the_post(); ?>
		<div class="entry-content-page">
			<?php the_content(); ?>
		</div>

	<?php
		endwhile;
	?>
	<br>
	<?php
	$type =  get_post_type();
	if($type == "page"){
	    $name = get_the_title();

	    if($name == "About us"){
		    $categories = get_terms(array( 'taxonomy' => ('project_skill')));
		    ?>
            <h3 id="skills">
                Available skills:
            </h3>
            
            <div id="tags">
            <ul>
		    <?php
		    foreach ($categories as $cat){
		        ?>

                        <li>
                            <a href="postgrid.php?category=<?php echo $cat->name;?>"><?php echo $cat->name?></a>
                        </li>

			    <?php
            }
            ?>
            </ul>
            </div>
            <?php

        }else{
	        //Is a page, but not About us
        }
    }else if($type == "project"){
	    //Is a project single
		$terms = wp_get_object_terms(get_the_ID(), 'project_skill');
		if(!empty($terms)){
		    ?>
            <h3 id="skills">
                Skills:
            </h3>
            <div id="tags">
            <ul>
            <?php
			foreach($terms as $term){
				$name = $term->name;
				?>
                    <li>
                        <a href="postgrid.php?category=<?php echo $name;?>"><?php echo $name?></a>
                    </li>

                <?php
			}
			?>
            </ul>
            </div>
    <?php
		}
    }else{
        //Is not a post nor a page
    }
	?>

</div>
