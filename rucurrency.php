<?php
/*
Plugin Name: Russian Currency Chart
Description: The plugin adds the widget which displays daily exchange rates of The Russian Central Bank for US Dollar and Euro along with the weekly chart.
Author: artbelov
Version: 0.6
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: russian-currency-chart
Domain Path: /languages
*/

function rucurrency_init() {
    wp_enqueue_style( 'style.css', plugin_dir_url( __FILE__ ) . '/style.css' );
    wp_register_script( 'Chart.js', plugin_dir_url( __FILE__ ) . '/vendor/Chart.min.js', null, '2.7.2', true);
    wp_enqueue_script( 'draw_charts.js', plugin_dir_url( __FILE__ ) . '/js/draw_charts.js', array( 'Chart.js', ), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'rucurrency_init' );

class Ru_Currency_Chart_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'ru_currency_chart',
            'Russian Currency Chart Widget',
            array( 'description' => __( 'Displays exchange rates for USD and EUR to Russian ruble along with the weekly chart.', 'russian-currency-chart' ) )
        ); 
    }

    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );

        echo $args['before_widget'];
        if ( ! empty( $title ) ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        $curr_data = array(
            'labels' => array_keys( $instance['c_data']['w_usd'] ),
            'usd_data' => array_values( $instance['c_data']['w_usd'] ),
            'eur_data' => array_values( $instance['c_data']['w_eur'] ),

            'show_by_def' => $instance['show_chart'],
            'chart_color' => $instance['chrt_color'],
        );

        wp_localize_script( 'draw_charts.js', 'curr_data', $curr_data );
        ?>

        <div class="ru-currency-chart_wrap">
            <div class="ru-currency-chart_body">

                <style type="text/css" scoped>
                    .ru-currency-chart_daily.current > .currency-sign svg { fill: <?php echo $instance['btn_color'];?>; }
                    .ru-currency-chart-draw_icon.on svg { fill: <?php echo $instance['btn_color'];?>; }
                </style>

                <div class="ru-currency-chart_daily_area">
                <div class="ru-currency-chart_daily <?php echo $instance['show_chart'] == '1' ? 'current' : ''; ?> " data-currency="usd">
                    <span class="currency-sign">
                        <?php echo file_get_contents( plugin_dir_url( __FILE__ ) . 'vendor/fontawesome-free/dollar-sign.svg' );?>
                    </span>
                    <span class="ru-currency-chart_value"><?php echo esc_html( str_replace( '.', ',', $instance['c_data']['d_usd'] ) ); ?></span>
                </div>
                <div class="ru-currency-chart_daily" data-currency="eur">
                    <span class="currency-sign">
                        <?php echo file_get_contents( plugin_dir_url( __FILE__ ) . 'vendor/fontawesome-free/euro-sign.svg' );?>
                    </span>
                    <span class="ru-currency-chart_value"><?php echo esc_html( str_replace( '.', ',', $instance['c_data']['d_eur'] ) ); ?></span>
                </div>
                <div class="ru-currency-chart-icon_wrap">
                    <span class="ru-currency-chart-draw_icon <?php echo $instance['show_chart'] == "1" ? 'on' : ''; ?> ">
                        <?php echo file_get_contents( plugin_dir_url( __FILE__ )  . 'vendor/fontawesome-free/chart-line.svg' ); ?>
                    </span>
                </div>
                </div>

                <canvas id="ru-currency-chart-id" width="800" height="400" <?php echo $instance['show_chart'] == "1" ? '' : 'style="display: none;"'; ?> ></canvas>
            </div>
        </div>


        <?php
        echo $args['after_widget'];
    
    }

    public function form( $instance ) {
        ?>

        <p>
            <label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title', 'russian-currency-chart' ); ?></label>
            <input class="widefat" name="<?php echo $this->get_field_name( 'title' );?>" id="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo ( isset( $instance['title'] ) ) ? $instance['title'] : ''; ?>">
        </p>

        <p>
            <input type="checkbox" class="" name="<?php echo $this->get_field_name( 'show_chart' );?>" id="<?php echo $this->get_field_name( 'show_chart' ); ?>" value="1" <?php checked( ( isset( $instance['show_chart'] ) ) ? $instance['show_chart'] : '', "1" ); ?>>
            <label for="<?php echo $this->get_field_name( 'show_chart' ); ?>"><?php _e( 'Show chart by default', 'russian-currency-chart' ); ?></label>
            <br>
        </p>

        <p>
            <input type="color" class="" name="<?php echo $this->get_field_name( 'btn_color' );?>" id="<?php echo $this->get_field_name( 'btn_color' );?>" value="<?php echo ( isset( $instance['btn_color'] ) ) ? $instance['btn_color'] : '#a5a5a5';?>">
            <label for="<?php echo $this->get_field_name( 'btn_color' ); ?>"><?php _e( 'Selection color', 'russian-currency-chart' ); ?></label>
            <br>
        </p>

        <p>
            <input type="color" class="" name="<?php echo $this->get_field_name( 'chrt_color' );?>" id="<?php echo $this->get_field_name( 'chrt_color' );?>" value="<?php echo ( isset( $instance['chrt_color'] ) ) ? $instance['chrt_color'] : '#8f6948';?>">
            <label for="<?php echo $this->get_field_name( 'chrt_color' ); ?>"><?php _e( 'Charts color', 'russian-currency-chart' ); ?></label>
            <br>
        </p>

        <?php

    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['title'] = isset( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['show_chart'] = isset( $new_instance['show_chart'] ) ? 
            sanitize_key( $new_instance['show_chart'] ) : '';

        $instance['btn_color'] = isset( $new_instance['btn_color'] )  ? 
            strip_tags( $new_instance['btn_color'] ) : '#ffffff';

        $instance['chrt_color'] = isset( $new_instance['chrt_color'] )  ? 
            strip_tags( $new_instance['chrt_color'] ) : '#ffffff';

        $instance['c_data'] = get_option( 'rucurrency_chart_data' );

        return $instance;
    }
}
add_action( 'widgets_init', function() {
    register_widget( 'Ru_Currency_Chart_Widget' );
} );

function rucurrency_chart_cron_init() {

    function update_currency_cron() {
        $result = array();

        $resp = wp_remote_get( 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=' . date( 'd/m/Y' ) );
        if ( is_array( $resp ) && ! is_wp_error( $resp ) ) {
            $xml_data = simplexml_load_string( $resp['body'] );

            if ( ! empty($xml_data) ) {
                $result['d_usd'] = sprintf( "%.2f", str_replace( ',', '.', 
                    (string) $xml_data->xpath( "/ValCurs/Valute[@ID='R01235']/Value" )[0] ) );
                $result['d_eur'] = sprintf( "%.2f", str_replace( ',', '.', 
                    (string) $xml_data->xpath( "/ValCurs/Valute[@ID='R01239']/Value" )[0] ) );
            } else {
                 $result['d_usd'] = $result['d_eur'] = null;
            }

        } else {
            $result['d_usd'] = $result['d_eur'] = null;
        }

        $start_date = date( 'd/m/Y', strtotime( '-1 week' ) );
        $end_date = date( 'd/m/Y' );
        $source = array();

        $source['w_usd'] = wp_remote_get( 
            "http://www.cbr.ru/scripts/XML_dynamic.asp?date_req1={$start_date}&date_req2={$end_date}&VAL_NM_RQ=R01235" ); 
        $source['w_eur'] = wp_remote_get( 
            "http://www.cbr.ru/scripts/XML_dynamic.asp?date_req1={$start_date}&date_req2={$end_date}&VAL_NM_RQ=R01239" ); 

        foreach ( $source as $v_key=>$v_data ) {
            if ( is_array( $v_data ) && ! is_wp_error( $v_data ) ) {
                $xml_data = simplexml_load_string( $v_data['body'] );

                if ( ! empty($xml_data) ) {

                    foreach ( $xml_data->children() as $rec ) {
                        $day = date( 'd M', strtotime( str_replace( '.', '-', (string) $rec['Date'] ) ) );
                        $cur_val = (float) sprintf( "%.2f", str_replace( ',', '.', (string) $rec->Value ) );

                        $result[ $v_key ][ $day ] = $cur_val;
                    }

                } else {
                    $result['w_usd'] = $result['w_eur'] = null;
                }

            } else {
                    $result['w_usd'] = $result['w_eur'] = null;
            }
        }

        update_option( 'rucurrency_chart_data', $result );
    }

    add_action( 'rucurrency_chart_cron_hook', 'update_currency_cron' );
    if ( ! wp_next_scheduled( 'rucurrency_chart_cron_hook' ) ) {
        wp_schedule_event( time(), 'daily', 'rucurrency_chart_cron_hook' );
    }

}
rucurrency_chart_cron_init();

function rucurrency_chart_deactivation() {
    delete_option( 'rucurrency_chart_data' );

    $t_stamp = wp_next_scheduled( 'rucurrency_chart_cron_hook' );
    wp_unschedule_event( $t_stamp, 'rucurrency_chart_cron_hook' );
}

function rucurrency_chart_load_plugin_textdomain() {
    load_plugin_textdomain( 'russian-currency-chart', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'rucurrency_chart_load_plugin_textdomain' );

//register_activation_hook( __FILE__, 'rucurrency_chart_init' );
register_deactivation_hook( __FILE__, 'rucurrency_chart_deactivation' );
