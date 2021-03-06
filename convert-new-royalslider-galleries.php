<?php
/*
Plugin Name: Convert New RoyalSlider Galleries to WordPress
Plugin URI: 
Description: Converts New RoyalSlider galleries to WordPress default galleries.
Version: 1.0
Author: Simon Archer
Author URI: http://www.archybold.com
License: GPL2

Based on a plugin by Stefan Senk  (email : info@senktec.com)

https://github.com/stefansenk/convert-nextgen-galleries

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function cnrs_admin_url() {
    return admin_url('options-general.php?page=convert-new-royalslider-galleries.php');
}

function cnrs_get_posts_to_convert_query($post_id = null, $max_number_of_posts = -1) {
    $args = array(
        's'           => '[new_royalslider',
        'post_type'   => array( 'post', 'page', 'legacy' ),
        'post_status' => 'any',
        'p' => $post_id,
        'posts_per_page' => $max_number_of_posts
    );
    return new WP_Query( $args );
}

function cnrs_find_gallery_shortcodes($post) {
    $matches = null;
    preg_match_all( '/\[new_royalslider.*?\]/si', $post->post_content, $matches );
    return $matches[0];
}

function cnrs_get_gallery_id_from_shortcode($shortcode) {
    $id = null;
    $idx = strpos($shortcode, 'id="');
    if ($idx !== false) {
        $idx += 4;
        $id = substr($shortcode, $idx, strpos($shortcode, '"', $idx) - $idx);
        $id = intval($id);
    }
    return $id;
}

function cnrs_list_galleries($posts_query) {
    echo '<h3>Listing ' . $posts_query->found_posts . ' posts with galleries to convert:</h3>';

    echo '<table>';
    echo '<tr>';
    echo '<th>Post ID</th>';
    echo '<th>Post Title</th>';
    echo '<th>Galleries</th>';
    echo '<th colspan="2">Actions</th>';
    echo '<tr>';
    foreach ( $posts_query->posts as $post ) {
        echo '<tr>';
        echo '<td>' . $post->ID . '</td>';
        echo '<td>' . $post->post_title . '</td>';
        echo '<td>';
        foreach ( cnrs_find_gallery_shortcodes($post) as $shortcode ) {
            echo $shortcode . '<br>';
        }
        echo '</td>';
        echo '<td><a href="' . admin_url('post.php?action=edit&amp;post=' . $post->ID) . '">Edit Post</a></td>';
        echo '<td><a href="' . cnrs_admin_url() . '&amp;action=convert&post=' . $post->ID . '" class="button">Convert</a></td>';
        echo '<tr>';
    }
    echo '</table>';
}

function cnrs_convert_galleries($posts_query) {
    set_time_limit( 1000 );
    
    global $wpdb;
    echo '<h3>Converting galleries in ' . $posts_query->found_posts . ' posts:</h3>';

    foreach ( $posts_query->posts as $post ) {
        echo '<h4>' . $post->post_title . '</h4>';
        foreach ( cnrs_find_gallery_shortcodes($post) as $shortcode ) {

            $gallery_id = cnrs_get_gallery_id_from_shortcode($shortcode);
            $slidesJson = $wpdb->get_var( $wpdb->prepare( "SELECT slides FROM {$wpdb->prefix}new_royalsliders WHERE id = %d", $gallery_id ) );

            $slides = json_decode($slidesJson, true);

            $attachment_ids = array();

            foreach ( $slides as $i => $slide ) {
                if (!array_key_exists('image', $slide) ||
                    !array_key_exists('attachment_id', $slide['image'])) {
                    echo "ERROR: no attached image for slide $i.<br>";
                    continue;
                }
                $id          = $slide['image']['attachment_id'];
                $title       = $slide['title'] ?: '';
                $description = $slide['description'] ?: '';
                $link        = $slide['link'] ?: '';

                array_push($attachment_ids, $id);
                $attachment = get_post($id);

                $altMeta = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
                if (!empty($title) && empty($altMeta)) {
                    update_post_meta($attachment->ID, '_wp_attachment_image_alt', $title);
                }

                if (!empty($title)) {
                    update_post_meta($attachment->ID, 'nrs_title', $title);
                }
                if (!empty($description)) {
                    update_post_meta($attachment->ID, 'nrs_description', $description);
                }
                if (!empty($link)) {
                    update_post_meta($attachment->ID, 'nrs_link', $link);
                }
            }

            if ( count( $attachment_ids ) == count( $slides ) ) {
                $new_shortcode = '[gallery columns="1" link="file" slider="new_royalslider" ids="'. implode( ',', $attachment_ids ) . '"]';
                $post->post_content = str_replace( $shortcode, $new_shortcode, $post->post_content );
                wp_update_post( $post );
                echo "Replaced <code>$shortcode</code> with <code>$new_shortcode</code>.<br>";
            } else {
                echo "<p>Not replacing <code>$shortcode</code>. " . count( $attachment_ids ) . " of " . count( $slides ) . " images converted.</p>";
            }
        }
    }
}

add_filter( 'plugin_action_links', function($links, $file) {
    if ( $file == 'convert-new-royalslider-galleries/convert-new-royalslider-galleries.php' ) {
        array_unshift( $links, '<a href="' . cnrs_admin_url() . '">' . __( 'Settings', 'convert-new-royalslider-galleries' ) . '</a>' );
    }
    return $links;
}, 10, 2 );

add_action('admin_menu', function() {
    add_options_page( 
        __( 'Convert New RoyalSlider Galleries', 'convert-new-royalslider-galleries' ),
        __( 'Convert New RoyalSlider Galleries', 'convert-new-royalslider-galleries' ),
        'manage_options', 'convert-new-royalslider-galleries.php', function() {

        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have permission to access this page.', 'convert-new-royalslider-galleries' ) );
        }
?>
        <div class="wrap">
            <h2><?php _e( 'Convert New RoyalSlider Galleries to WordPress', 'convert-new-royalslider-galleries' ); ?></h2>
            <?php 
                $post_id = isset($_GET['post']) ? $_GET['post'] : null;
                $max_num_to_convert = isset($_GET['max_num']) ? $_GET['max_num'] : -1;

                $posts_to_convert_query = cnrs_get_posts_to_convert_query( $post_id, $max_num_to_convert );

                if ( isset( $_GET['action'] ) ) {
                    if ( $_GET['action'] == 'list' ) {
                        cnrs_list_galleries($posts_to_convert_query);
                    } elseif ( $_GET['action'] == 'convert' ) {
                        cnrs_convert_galleries($posts_to_convert_query);
                    }
                } else {
                    echo '<h3>' . $posts_to_convert_query->found_posts . ' posts with galleries to convert</h3>';
                }
            ?>
            <p><a class="" href="<?php echo cnrs_admin_url() . '&amp;action=list' ?>">List galleries to convert</a></p>
            <p><a class="button" href="<?php echo cnrs_admin_url() . '&amp;action=convert' ?>">Convert all galleries</a></p>
        </div>  
<?php
    });
});
