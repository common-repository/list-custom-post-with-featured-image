<?php
/*
Plugin Name: List Custom Post with featured image
Plugin URI: https://wordpress.org/plugins/list-custom-post-with-featured-image/
Description: Showing custom Post with featured images with title on gallery, Short-code: [LCPOSTLIST post_type="post" limit=4 order="ASC"]
Author: Vikas Sharma
Author URI: https://profiles.wordpress.org/devikas301
Version: 1.2
License: GPLv2
*/

define('LCPFI_PATH', plugin_dir_path(__FILE__));
define('LCPFI_LINK', plugin_dir_url(__FILE__));
define('LCPFI_PLUGIN_NAME', plugin_basename(__FILE__));

  /******post-list**********/ 
  function lcpfi_listpost( $lcpfi_atts ){
	  
	global $post, $wpdb;	
	$lcpfi_args = shortcode_atts([
        'post_type' => 'post',
		'limit'     => '5',  
        'order'	    => 'DESC',
		'pagination'=> 'on'
	   ], $lcpfi_atts);		
	 
	$lcpfi_pggp = explode('/page/', $_SERVER['REQUEST_URI']);
	 
	if(isset($lcpfi_pggp[1])){
		$lcpfi_pggpcount = explode('/', $lcpfi_pggp[1]); 		
		$lcpfi_gvpage = $lcpfi_pggpcount[0];		
	} else {		 
	  $lcpfi_gvpage = 1;	
	}
	 
	$lcpfi_paged = (get_query_var('paged')) ? get_query_var('paged') : ((get_query_var('page')) ? get_query_var('page') : 1);
	  
    $lcpfi_postargs = [
	        'post_type'     => $lcpfi_args['post_type'],
            'post_status'   => 'publish', 		  
	        'posts_per_page'=> $lcpfi_args['limit'], 
			'paged'         => $lcpfi_paged,			 
			'order'         => $lcpfi_args['order'],	
            'page'          => $lcpfi_paged
	  ];
	  //'offset'         => 0, 		
	  
	$lcpfi_gp = new WP_Query($lcpfi_postargs); 
	
	if($lcpfi_gp->have_posts()): 
		
	 $lcpfi_size = 'medium';	
	 $lcpfi_gpost = '<div class="lcpfi-post-gallery"><div class="row">'; 
	
	 while($lcpfi_gp->have_posts()) : $lcpfi_gp->the_post();	  
	  
	   $lcpfi_pid = get_the_ID();	 
	  
      if(has_post_thumbnail($lcpfi_pid)){       
        $lcpfi_pimage = get_the_post_thumbnail($lcpfi_pid, $lcpfi_size);     
      } else {		  
		$lcpfi_pimage = '<img src="'.LCPFI_LINK.'images/default-placeholder.png" alt="'.get_the_title().'" class="default-image attachment-medium size-medium wp-post-image" style="width:284px">';  		  
	  }		  
	  
	  $lcpfi_gpost .= '<div class="col-md-3 lcpfi-section"><div class="wpb_wrapper"><div class="lcpfi-wrapper"><a href="'.get_the_permalink().'">'.$lcpfi_pimage.'</a></div><h4><a href="'.get_the_permalink().'">'.get_the_title().'</a></h4></div></div>';
		
	 endwhile;     
	 
	 $lcpfi_gpost .= '</div>';
	 if($lcpfi_args['pagination'] == 'on'){
	  if(function_exists('lcpfi_pagination')){
       $lcpfi_gpost .= '<div class="clear lcpfi-pagination-section">'.lcpfi_pagination($lcpfi_gp->max_num_pages,"",$lcpfi_paged).'</div>';
      }	 
	 }
	 $lcpfi_gpost .= '</div>';	
	  
	  wp_reset_postdata();
	  return $lcpfi_gpost;
	endif;	
  }
  
 add_shortcode('LCPOSTLIST', 'lcpfi_listpost');     
 
 function lcpfi_pagination($numpages='', $pagerange='', $paged=''){
  if(empty($pagerange)){
    $pagerange = 2;
  } 
  
  if(empty($paged)){
   global $paged;
    $paged = 1;
  }
  if($numpages == ''){
    global $wp_query;
    $numpages = $wp_query->max_num_pages;
    if(!$numpages){
        $numpages = 1;
    }
  }

  /** 
   * We construct the pagination arguments to enter into our paginate_links
   * function. 
   */
  $pagination_args = [
    'base'         => get_pagenum_link(1).'%_%',
    'format'       => 'page/%#%',
    'total'        => $numpages,
    'current'      => $paged,
    'show_all'     => False,
    'end_size'     => 1,
    'mid_size'     => $pagerange,
    'prev_next'    => True,
    'prev_text'    => __('&laquo;'),
    'next_text'    => __('&raquo;'),
    'type'         => 'plain',
    'add_args'     => false,
    'add_fragment' => ''
  ];

  $lcpfi_paginate_links = paginate_links($pagination_args);

  if($lcpfi_paginate_links){
     $lcpfi_pgdata = "<nav class='lcpfi-glpagination'>";
      // echo "<span class='page-numbers page-num'>Page " . $paged . " of " . $numpages . "</span> ";
     $lcpfi_pgdata .= $lcpfi_paginate_links;
     $lcpfi_pgdata .= "</nav>";
	return $lcpfi_pgdata;
  }
 }
 
 add_action( 'wp_enqueue_scripts', 'lcpfi_enqueue_styles' );
 function lcpfi_enqueue_styles(){     
   global $wp_styles;
   wp_register_style('LCPFI', LCPFI_LINK. "css/lcpfi_style.css");	
   wp_enqueue_style('LCPFI');
 }
?>