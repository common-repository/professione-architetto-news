<?php
/**
 * Plugin Name: professione Architetto News
 * Plugin URI: http://www.professionearchitetto.it/banner/
 * Description: Pubblica sul tuo sito web le ultime notizie di professione Architetto.
 * Version: 0.2
 * Author: redazione professione Architetto
 * Author URI: http://professionearchitetto.it/
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: professionearchitetto-news
 * Domain Path: /languages

*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( !class_exists( 'professioneArchitetto_news_RSS' ) ) :
define('PROF_ARCH_NEWS', 'professionearchitetto-news');

class professioneArchitetto_news_RSS extends WP_Widget {
	public $plugin_basename;
	public $plugin_url;
	public $plugin_path;
	public $feedurl = 'http://www.professionearchitetto.it/rss/panews/';
	public $defaultTitle = 'professioneArchitetto News';
	public $version = '0.1';

	function __construct() {		
		$widget_ops = array( 'description' => __("Pubblica sul tuo sito web le ultime notizie di professione Architetto", PROF_ARCH_NEWS) );
		parent::__construct('professioneArchitetto_news', 'professioneArchitetto News', $widget_ops);
	}

	public function widget( $args, $instance ) {
		if ( isset($instance['error']) && $instance['error'] ) return;
	
		$url = 'https://www.professionearchitetto.it/rss/panews/';
		if( !empty($instance['idcategoria']) ) {
			$catselected = $instance['idcategoria'];
			if(count($catselected) < 6) {
				$cats = array();
				foreach($catselected as $key => $value) {
					$cats[] = $key;
				}
				$url .= "?IDCategoria=" . implode(",",$cats);
			}
		}
		
		$rss = fetch_feed($url);
		$title = $instance['title'];
		$desc = '';
		$link = '';
 
		if ( ! is_wp_error($rss) ) {
			$desc = esc_attr(strip_tags(@html_entity_decode($rss->get_description(), ENT_QUOTES, get_option('blog_charset'))));
			if ( empty($title) ) $title = strip_tags( $rss->get_title() );
			$link = strip_tags( $rss->get_permalink() );
			while ( stristr($link, 'http') != $link ) $link = substr($link, 1);
		}
 
		if(empty($title)) $title = $desc;
 
		$title = '<a class="rsswidget" href="' . esc_url( $link ) . '">'. esc_html( $title ) . '</a>'; 
		echo $args['before_widget'];
		echo $args['before_title'] . $title . $args['after_title'];
		//echo $url;
		$this->rss_output( $rss, $instance );
		echo $args['after_widget'];
 
		if ( ! is_wp_error($rss) ) $rss->__destruct();
		unset($rss);
	}
 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => $this->defaultTitle, 'idcategoria' => '', 'items' => 5, 'error' => false, 'show_icon' => 0, 'show_summary' => 0, 'show_date' => 0 ));
		$instance['title'] = strip_tags($new_instance['title']);
		
		$instance['idcategoria'] = $new_instance['idcategoria'];
		$instance['items'] = strip_tags($new_instance['items']);
		$instance['error'] = strip_tags($new_instance['error']);
		$instance['show_icon'] = strip_tags($new_instance['show_icon']);
		$instance['show_summary'] = strip_tags($new_instance['show_summary']);
		$instance['show_date'] = strip_tags($new_instance['show_date']);
		return $instance;
	}

	function form( $args ) {
		$args = wp_parse_args( (array) $args, array( 'title' => $this->defaultTitle, 'idcategoria' => '', 'items' => 5, 'error' => false, 'show_icon' => 0, 'show_summary' => 0, 'show_date' => 0 ) );
		$title = strip_tags($args['title']);
		$args['number'] = $this->number;
		$args['url'] = $this->feedurl;

    $esc_number = esc_attr( $args['number'] );
		
		$catselected = $args['idcategoria'];
		
?>
    <p><label for="rss-title-<?php echo $esc_number; ?>"><?php _e( 'Inserisci un titolo per il widget (opzionale):', PROF_ARCH_NEWS ); ?></label>
    <input class="widefat" id="rss-title-<?php echo $esc_number; ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $args['title'] ); ?>" /></p>
    <p><?php _e( 'Indica le categorie di interesse:', PROF_ARCH_NEWS ); ?>
    <?php
			$NomiCategorie = array(1 => "Mostre e Convegni", 2 => "Formazione &amp; Corsi", 3 => "Progetti di Architettura", 4=> "Viaggi e Architetture", 5=> "Concorsi di Architettura", 21=> "Professione");
			foreach($NomiCategorie as $key => $value) {
				$chk = isset($catselected[$key]) ? ' checked="checked"' : "";
				echo '<br><label><input type="checkbox" value="1" id="rss-idcategoria-' . $key . '" name="' . $this->get_field_name('idcategoria'). '[' . $key . ']" ' . $chk . '/> ' . $value . '</label> ';
			}
    ?>
    </p>
    <p><label for="rss-items-<?php echo $esc_number; ?>"><?php _e( 'Quanti annunci mostrare?', PROF_ARCH_NEWS ); ?></label>
    <select id="rss-items-<?php echo $esc_number; ?>" name="<?php echo $this->get_field_name('items'); ?>">
    <?php
    for ( $i = 1; $i <= 20; ++$i ) {
        echo "<option value='$i' " . selected( $args['items'], $i, false ) . ">$i</option>";
    }
    ?>
    </select></p>
    <p><input id="rss-show-icon-<?php echo $esc_number; ?>" name="<?php echo $this->get_field_name('show_icon'); ?>" type="checkbox" value="1" <?php checked( $args['show_icon'] ); ?> />
    <label for="rss-show-icon-<?php echo $esc_number; ?>"><?php _e( "Mostrare l'icona?", PROF_ARCH_NEWS ); ?></label></p>
    <p><input id="rss-show-summary-<?php echo $esc_number; ?>" name="<?php echo $this->get_field_name('show_summary'); ?>" type="checkbox" value="1" <?php checked( $args['show_summary'] ); ?> />
    <label for="rss-show-summary-<?php echo $esc_number; ?>"><?php _e( 'Mostrare il dettaglio?', PROF_ARCH_NEWS ); ?></label></p>
    <p><input id="rss-show-date-<?php echo $esc_number; ?>" name="<?php echo $this->get_field_name('show_date'); ?>" type="checkbox" value="1" <?php checked( $args['show_date'] ); ?>/>
    <label for="rss-show-date-<?php echo $esc_number; ?>"><?php _e( 'Mostrare la data di pubblicazione?', PROF_ARCH_NEWS ); ?></label></p>
<?php
	}

	private function rss_output( $rss, $args = array() ) {
    if ( is_string( $rss ) ) {
        $rss = fetch_feed($rss);
    } elseif ( is_array($rss) && isset($rss['url']) ) {
        $args = $rss;
        $rss = fetch_feed($rss['url']);
    } elseif ( !is_object($rss) ) {
        return;
    }
 
    if ( is_wp_error($rss) ) {
        //if ( is_admin() || current_user_can('manage_options') )
            //echo '<p>' . sprintf( __('<strong>RSS Error</strong>: %s', PROF_ARCH_NEWS), $rss->get_error_message() ) . '</p>';
        return;
    }
 
    $default_args = array( 'show_author' => 0, 'show_date' => 0, 'show_icon' => 0, 'show_summary' => 0, 'items' => 0 );
    $args = wp_parse_args( $args, $default_args );
 
    $items = (int) $args['items'];
    if ( $items < 1 || 20 < $items ) $items = 10;
    $show_icon  = (int) $args['show_icon'];
    $show_summary  = (int) $args['show_summary'];
    $show_author   = (int) $args['show_author'];
    $show_date     = (int) $args['show_date'];
 
    if ( !$rss->get_item_quantity() ) {
        echo '<ul><li>' . __( 'An error has occurred, which probably means the feed is down. Try again later.', PROF_ARCH_NEWS ) . '</li></ul>';
        $rss->__destruct();
        unset($rss);
        return;
    }
		 
    echo '<ul>';
    foreach ( $rss->get_items( 0, $items ) as $item ) {

        $link = $item->get_link();
        while ( stristr( $link, 'http' ) != $link ) {
            $link = substr( $link, 1 );
        }
        $link = esc_url( strip_tags( $link ) );
 
        $title = esc_html( trim( strip_tags( $item->get_title() ) ) );
        if ( empty( $title ) ) {
            $title = __( 'Untitled', PROF_ARCH_NEWS );
        }
 

        $desc = @html_entity_decode( $item->get_description(), ENT_QUOTES, get_option( 'blog_charset' ) );
        //$desc = esc_attr( wp_trim_words( $desc, 55, ' [&hellip;]' ) );
 
        $summary = '';
        if ( $show_summary ) {
            $summary = $desc;
 
            // Change existing [...] to [&hellip;].
            if ( '[...]' == substr( $summary, -5 ) ) {
                $summary = substr( $summary, 0, -5 ) . '[&hellip;]';
            }
 
            //$summary = '<div class="rssSummary">' . esc_html( $summary ) . '</div>';
            $summary = '<div class="rssSummary">' . $summary . '</div>';
        }
				
        $image = "";
        $icon = '';
				$enc = $item->get_enclosure();
				if(isset($enc)) $image = $enc->get_thumbnail();
        if ( $show_icon && !empty($image) ) {
            $icon = '<div class="rssIcon"><img src="' . $image . '"></div>';
        }

        $date = '';
        if ( $show_date ) {
            $date = $item->get_date( 'U' );
 
            if ( $date ) {
                $date = ' <div class="rss-date">' . date_i18n( get_option( 'date_format' ), $date ) . '</div>';
            }
        }
 
        $author = '';
        if ( $show_author ) {
            $author = $item->get_author();
            if ( is_object($author) ) {
                $author = $author->get_name();
                $author = ' <cite>' . esc_html( strip_tags( $author ) ) . '</cite>';
            }
        }
 
        if ( $link == '' ) {
            echo "<li>$icon$title{$date}{$summary}{$author}</li>";
        } elseif ( $show_summary ) {
            echo "<li><a class='rsswidget' href='$link'>$icon$title</a>{$date}{$summary}{$author}</li>";
        } else {
            echo "<li><a class='rsswidget' href='$link'>$icon$title</a>{$date}{$author}</li>";
        }
    }
    echo '</ul>';
    $rss->__destruct();
    unset($rss);
	}

}
endif;

function professioneArchitetto_news_RSS_Register() {
	$locale = apply_filters( 'plugin_locale', get_locale(), PROF_ARCH_NEWS );
	load_plugin_textdomain( PROF_ARCH_NEWS, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	return register_widget( "professioneArchitetto_news_RSS" );
}
add_action( 'widgets_init', 'professioneArchitetto_news_RSS_Register' );
