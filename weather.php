<?php
/**
 * Plugin Name: WeatherAPI Shortcode
 * Description: Displays current weather via shortcode [weather_city city="YourCity"] with beautiful styling.
 * Version: 1.1
 * Author: Grok Assistant
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WeatherAPI_Shortcode {
    private $api_key_option = 'weatherapi_key';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_shortcode('weather_city', [$this, 'shortcode_handler']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    // Add settings page
    public function add_settings_page() {
        add_options_page(
            'WeatherAPI Settings',
            'WeatherAPI',
            'manage_options',
            'weatherapi-settings',
            [$this, 'settings_page_html']
        );
    }

    // Settings page HTML
    public function settings_page_html() {
        if (isset($_POST['weatherapi_key'])) {
            update_option($this->api_key_option, sanitize_text_field($_POST['weatherapi_key']));
            echo '<div class="notice notice-success"><p>API Key saved!</p></div>';
        }
        $key = get_option($this->api_key_option, '');
        ?>
        <div class="wrap">
            <h1>WeatherAPI Settings</h1>
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row">API Key</th>
                        <td>
                            <input type="text" name="weatherapi_key" value="<?php echo esc_attr($key); ?>" class="regular-text" />
                            <p class="description">Enter your WeatherAPI.com key (get one at <a href="https://www.weatherapi.com" target="_blank">weatherapi.com</a>).</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    // Enqueue CSS
    public function enqueue_styles() {
        wp_enqueue_style(
            'weatherapi-styles',
            plugin_dir_url(__FILE__) . 'weatherapi-styles.css',
            [],
            '1.1'
        );
    }

    // Shortcode handler
    public function shortcode_handler($atts) {
        $atts = shortcode_atts(['city' => 'London'], $atts);
        $city = sanitize_text_field($atts['city']);
        $key = get_option($this->api_key_option, '');

        if (empty($key)) {
            return '<p class="weatherapi-error">WeatherAPI Error: Please enter your API key in Settings > WeatherAPI.</p>';
        }

        $transient_key = 'weather_' . md5($city);
        $cached = get_transient($transient_key);

        if ($cached !== false) {
            return $cached;
        }

        $url = "https://api.weatherapi.com/v1/current.json?key={$key}&q=" . urlencode($city);
        $response = wp_remote_get($url, ['timeout' => 10]);

        if (is_wp_error($response)) {
            return '<p class="weatherapi-error">WeatherAPI Error: Unable to fetch data. Please try again later.</p>';
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            return '<p class="weatherapi-error">WeatherAPI Error: ' . esc_html($data['error']['message']) . '</p>';
        }

        if (!isset($data['current'])) {
            return '<p class="weatherapi-error">WeatherAPI Error: Invalid response for city: ' . esc_html($city) . '</p>';
        }

        $current = $data['current'];
        $condition = $current['condition']['text'];
        $icon = $current['condition']['icon'];
        $temp_c = $current['temp_c'];
        $feelslike_c = $current['feelslike_c'];
        $wind_kph = $current['wind_kph'];
        $humidity = $current['humidity'];

        $output = '
        <div class="weatherapi-card">
            <h3 class="weatherapi-title">Weather in ' . esc_html($city) . '</h3>
            <div class="weatherapi-main">
                <img src="' . esc_url($icon) . '" alt="' . esc_attr($condition) . '" class="weatherapi-icon" />
                <div class="weatherapi-temp">' . esc_html($temp_c) . '&deg;C</div>
            </div>
            <p class="weatherapi-condition">' . esc_html($condition) . '</p>
            <div class="weatherapi-details">
                <p><span class="weatherapi-label">Feels Like:</span> ' . esc_html($feelslike_c) . '&deg;C</p>
                <p><span class="weatherapi-label">Wind:</span> ' . esc_html($wind_kph) . ' km/h</p>
                <p><span class="weatherapi-label">Humidity:</span> ' . esc_html($humidity) . '%</p>
            </div>
            <small class="weatherapi-updated">Last updated: ' . esc_html($current['last_updated']) . '</small>
        </div>';

        set_transient($transient_key, $output, 600);
        return $output;
    }
}

// Instantiate the class
new WeatherAPI_Shortcode();
?>
