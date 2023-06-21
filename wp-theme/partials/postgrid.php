<?php


    if(isset($_GET["filter"]) && !empty($_GET["filter"])){
        $taxonomy = $_GET["filter"];
        $auxTaxonomy = $taxonomy;
    }else{
        $taxonomy = get_terms(array( 'taxonomy' => ("project_type"),'hide_empty' => 0, 'fields' => 'names' ));
    }
    if(isset($_GET["category"]) && !empty($_GET["category"])) {
	    $category = $_GET["category"];
    }else{
        $category = null;
    }
    if($category == null){
	    $args = array( 'post_type' => 'project', 'tax_query' => array( array( 'taxonomy' => 'project_type','field' => 'name','terms' => $taxonomy ) ) );
    }else{
	    $args = array( 'post_type' => 'project', 'tax_query' => array( array( 'taxonomy' => 'project_skill','field' => 'name','terms' => $category ) ) );
    }

    $results = new WP_Query( $args );


    $categories = get_terms(array( 'taxonomy' => ("project_type")));
    ?>
    <div id="filter">
    <ul>
    <?php
    $i = 0;
    foreach ($categories as $cat){
        ?>

                <?php if($i == 0){
                    ?>
                                    <li>
                                        <a href='?filter='>All</a>
                                    </li>
                    <?php
                }
                ?>
                <li>
                    <?php
                    $name = $cat->name;
                    if($auxTaxonomy != null){
                        if($auxTaxonomy == $name){
                            echo '<a href=?filter=' .$name .'> '.$name.'</a>';
                        }
                    }else{
	                    echo '<a href=?filter=' .$name .'> '.$name.'</a>';
                    }
                    ?>
                </li>

        <?php
        $i++;
    }
    ?>
     </ul>
        </div>
    <?php
    echo "<br>";

    if ( ! $results ){
	    echo "No posts found";
    }
    while ( $results->have_posts() ) {
	    $results->the_post();

    ?>
    <div class="gallery">
        <a href=<?php the_permalink() ?>>
    <?php the_post_thumbnail('grid_thumbnail'); ?>
        </a>
        <div class="desc"><?php the_title() ?></div>
        </div>

    <?php
    }

?>