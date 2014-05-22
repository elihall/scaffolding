<?php
/*
Template Name: Site Map Page
*/
get_header();

//collect custom post types
$custom_types=array();
foreach( get_post_types( array('public' => true) ) as $post_type ) {
	if ( in_array( $post_type, array('post','page','attachment') ) )
	continue;

	//collect the custom post types
	$pt = get_post_type_object($post_type);
	array_push(
		$custom_types,
		array(
			'post_type' => $pt,
			'posts' => get_posts(array(
				'numberposts'=> -1, //get all
				'offset'=> 0,
				'category'=> '',
				'orderby'=> 'menu_order post_date',
				'order'=> 'DESC',
				'meta_key'=>'',
				'meta_value'=> '',
				'meta_query'=> array(),
				'post_type'=> $pt->name,
				'post_mime_type'=> '',
				'post_parent'=> '',
				'post_status'=> 'publish',
				'suppress_filters'=> true
			))
		)
	);
}

//Collect all the id's of post that have noindex set to exclude them from the site map
$excluded_posts = get_posts(array(
	'numberposts'=> -1, //get all
	'offset'=> 0,
	'meta_value'=> '',
	'meta_query'=> array(
		array(
			'key'     => '_yoast_wpseo_meta-robots-noindex', //remove post with exclude set to yes
			'value'   => '1',
			'compare' => '='
		)
	),
	'post_type'=> get_post_types(array('public' => true)),
	'post_status'=> 'publish'
));
//collect all the excluded post ids
$excluded_posts_IDs = array();
foreach($excluded_posts as $excluded_post){
	array_push($excluded_posts_IDs,$excluded_post->ID);
}
array_push($excluded_posts_IDs,get_the_ID()); //add the current sitemap page id
$excluded_IDs = implode(',',$excluded_posts_IDs); //convert to a comma (,) separated string
?>

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> role="article">

				<header class="article-header">

					<h1 class="entry-title page-title"><?php the_title(); ?></h1>

				</header>

				<section class="entry-content clearfix">
					
					<?php the_content(); ?>
					
				</section>
				
			</article>
					
			<div class="row">

				<div class="col-sm-6">
					
					<div id="pages">
						<h2 class="section-title">Pages</h2>
						<ul>
							<?php 
							wp_list_pages(
								array(
									'title_li' 	=> '', 
									'exclude' 	=> $excluded_IDs
								) 
							); 
							?>
						</ul>
					</div> <?php // end #pages ?>
					
				</div> <?php // end .col-sm-6 ?>

				<div class="col-sm-6 clearfix">
					
					<div id="posts">
						<h2 class="section-title">Posts</h2>
						<ul>
							<?php
							$count_posts = wp_count_posts();
							$published_posts = $count_posts->publish;
							$read_settings_num_posts = get_option('posts_per_page');
							
							$new_posts = get_posts(
								array(
									'numberposts'	=> $read_settings_num_posts,
									'orderby' 		=> 'post_date',
									'order' 		=> 'DESC',
									'offset'		=> 0,
									'meta_value'	=> '',
									'meta_query'	=> array(
										array(
											'key'     => '_yoast_wpseo_meta-robots-noindex', //remove post with exclude set to yes
											'value'   => '',
											'compare' => 'NOT EXISTS'
										)
									),
									'post_type'		=> 'post',
									'post_status'	=> 'publish'
							));
							
							foreach($new_posts as $new_post) : ?>
							
							<li>
								<a title="<?php echo get_the_title($new_post->ID); ?>" href="<?php echo get_permalink($new_post->ID); ?>"><?php echo get_the_title($new_post->ID); ?></a>
							</li>
							
							<?php 
							endforeach;
							wp_reset_query(); //Restore global post data ?>
						
							<?php 
							/* Show More Link if the total number of Published Posts is greater than the Read Settings number of posts */
							if ($published_posts > $read_settings_num_posts) : ?>
							<li>
								<a title="View All Blog Posts" href="<?php echo get_permalink( get_option( 'page_for_posts' ) ); ?>">More...</a>
							</li>
							<?php
							endif; ?>

						</ul>
					</div> <?php // end #posts ?>
					
					<div id="categories">
						<h2 class="section-title">Categories</h2>
						<ul>
							<?php 
							wp_list_categories(
								array( 
									'title_li' 	=> ''
								)
							); 
							?>
						</ul>
					</div> <?php // end #categories ?>
					
					<div id="tags">
						<h2 class="section-title">Tags</h2>
						<ul>
							<?php wp_tag_cloud(); ?>
						</ul>
					</div> <?php // end #tags ?>
					
					<div id="archives">
						<h2 class="section-title">Archives</h2>
						<ul>
							<?php 
							wp_get_archives(
								array(
									'type'			  => 'monthly',
									'show_post_count' => true
								)
							); 
							?>
						</ul>
					</div> <?php // end #archives ?>
					
				</div> <?php // end .col-sm-6 ?>
				
			</div> <?php // end .row ?>
			
		<?php endwhile; ?>

		<?php else : ?>

			<?php get_template_part('includes/template','error'); // WordPress template error message ?>

		<?php endif; ?>
			
<?php get_footer();
