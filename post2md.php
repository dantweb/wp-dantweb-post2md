<?php
/**
 * Plugin Name: WP Markdown Exporter
 * Description: Export posts to Markdown by tag, category, or date range as a ZIP archive.
 * Version: 1.0.1
 * Author: Your Name
 * Text Domain: wp-md-exporter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Ensure Parsedown is available via Composer autoload
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    add_action( 'admin_notices', function() {
        echo '<div class="notice notice-error"><p>WP Markdown Exporter: please run <code>composer install</code> in the plugin directory to install dependencies.</p></div>';
    } );
    return;
}

use Parsedown;

class WP_MD_Exporter {
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_post_wpmde_export', [ $this, 'process_export' ] );
    }

    public function add_admin_menu() {
        add_menu_page(
            'Markdown Exporter',
            'MD Exporter',
            'manage_options',
            'wp-md-exporter',
            [ $this, 'settings_page' ],
            'dashicons-download',
            80
        );
    }

    public function settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Fetch tags and categories
        $tags = get_tags( array( 'hide_empty' => false ) );
        $cats = get_categories( array( 'hide_empty' => false ) );
        ?>
        <div class="wrap">
            <h1>Markdown Exporter</h1>
            <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                <?php wp_nonce_field( 'wpmde_export_action', 'wpmde_export_nonce' ); ?>
                <input type="hidden" name="action" value="wpmde_export">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="wpmde_tag">Tag</label></th>
                        <td>
                            <select id="wpmde_tag" name="wpmde_tag">
                                <option value="">— Any —</option>
                                <?php foreach ( $tags as $t ): ?>
                                    <option value="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpmde_category">Category</label></th>
                        <td>
                            <select id="wpmde_category" name="wpmde_category">
                                <option value="">— Any —</option>
                                <?php foreach ( $cats as $c ): ?>
                                    <option value="<?php echo esc_attr( $c->slug ); ?>"><?php echo esc_html( $c->name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Date From</th>
                        <td><input type="date" name="wpmde_date_from"></td>
                    </tr>
                    <tr>
                        <th scope="row">Date To</th>
                        <td><input type="date" name="wpmde_date_to"></td>
                    </tr>
                    <tr>
                        <th scope="row">Zip Filename</th>
                        <td><input type="text" name="wpmde_filename" placeholder="Leave blank for YYYY-MM-DD.zip"></td>
                    </tr>
                </table>
                <?php submit_button( 'Generate ZIP' ); ?>
            </form>
        </div>
        <?php
    }

    public function process_export() {
        // Verify permissions & nonce
        if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'wpmde_export_action', 'wpmde_export_nonce' ) ) {
            wp_die( 'Permission denied.' );
        }

        // Sanitize input
        $tag      = sanitize_text_field( $_POST['wpmde_tag'] );
        $cat      = sanitize_text_field( $_POST['wpmde_category'] );
        $from     = sanitize_text_field( $_POST['wpmde_date_from'] );
        $to       = sanitize_text_field( $_POST['wpmde_date_to'] );
        $filename = sanitize_text_field( $_POST['wpmde_filename'] );

        // Build query args
        $args = [ 'post_type' => 'post', 'posts_per_page' => -1 ];
        if ( $tag ) {
            $args['tag'] = $tag;
        }
        if ( $cat ) {
            $args['category_name'] = $cat;
        }
        if ( $from || $to ) {
            $args['date_query'] = [];
            if ( $from ) {
                $args['date_query'][] = [ 'after' => $from ];
            }
            if ( $to ) {
                $args['date_query'][] = [ 'before' => $to ];
            }
        }

        $posts = get_posts( $args );
        if ( empty( $posts ) ) {
            wp_die( 'No posts found for the selected criteria.' );
        }

        // Prepare ZIP name
        $date = date( 'Y-m-d' );
        $zip_name = $filename ? $filename : "{$date}.zip";

        // Create ZIP
        $tmp_file = tempnam( sys_get_temp_dir(), 'wpmde' );
        $zip = new ZipArchive;
        if ( $zip->open( $tmp_file, ZipArchive::CREATE ) !== true ) {
            wp_die( 'Could not create ZIP archive.' );
        }

        $parsedown = new Parsedown();
        $index_lines = [];

        foreach ( $posts as $post ) {
            setup_postdata( $post );
            $md  = "# " . $post->post_title . "\n\n";
            $md .= $parsedown->text( $post->post_content ) . "\n\n";
            $md .= "[Original Post]({$post->guid})\n";

            $category = get_the_category( $post->ID );
            $cat_slug = $category ? $category[0]->slug : 'uncategorized';
            $folder   = "content/{$cat_slug}";
            $file     = "{$folder}/{$post->post_name}.md";

            $zip->addFromString( $file, $md );
            $index_lines[] = "- [{$post->post_title}]({$file})";
        }
        wp_reset_postdata();

        // Add index.md
        $index = implode("\n", $index_lines);
        $zip->addFromString( 'index.md', $index );
        $zip->close();

        // Send ZIP and exit immediately
        header( 'Content-Type: application/zip' );
        header( 'Content-Disposition: attachment; filename="' . esc_attr( $zip_name ) . '"' );
        header( 'Content-Length: ' . filesize( $tmp_file ) );
        readfile( $tmp_file );
        unlink( $tmp_file );
        exit;
    }
}

new WP_MD_Exporter();
