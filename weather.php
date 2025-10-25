<?php
/**
 * Plugin Name: WeatherAPI Shortcode
 * Description: Integrates WeatherAPI.com to display current weather via shortcode [weather_city city="YourCity"].
 * Version: 1.0
 * Author: Grok Assistant
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add settings page
add_action('admin_menu', 'weatherapi_add_settings_page');
function weatherapi_add_settings_page() {
    add_options_page(
        'WeatherAPI Settings',
        'WeatherAPI',
        'manage_options',
        'weatherapi-settings',
        'weatherapi_settings_page'
    );
}

// Settings page HTML
function weatherapi_settings_page() {
    if (isset($_POST['weatherapi_key'])) {
        update_option('weatherapi_key', sanitize_text_field($_POST['weatherapi_key']));
        echo '<div class="notice notice-success"><p>API Key saved!</p></div>';
    }
    $key = get_option('weatherapi_key', '');
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

// Register shortcode
add_shortcode('weather_city', 'weatherapi_shortcode_handler');
function weatherapi_shortcode_handler($atts) {
    $atts = shortcode_atts(array(
        'city' => 'London', // Default city
    ), $atts);

    $city = sanitize_text_field($atts['city']);
    $key = 'bdabe763ab4c3757cd2754c5af5148ec';

    if (empty($key)) {
        return '<p><strong>WeatherAPI Error:</strong> Please enter your API key in Settings > WeatherAPI.</p>';
    }

    $transient_key = 'weather_' . md5($city);
    $cached = get_transient($transient_key);

    if ($cached !== false) {
        return $cached;
    }

    $url = "https://api.weatherapi.com/v1/current.json?key={$key}&q=" . urlencode($city);
    $response = wp_remote_get($url, array('timeout' => 10));

    if (is_wp_error($response)) {
        return '<p><strong>WeatherAPI Error:</strong> Unable to fetch data. Please try again later.</p>';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['error'])) {
        return '<p><strong>WeatherAPI Error:</strong> ' . esc_html($data['error']['message']) . '</p>';
    }

    if (!isset($data['current'])) {
        return '<p><strong>WeatherAPI Error:</strong> Invalid response for city: ' . esc_html($city) . '</p>';
    }

    $current = $data['current'];
    $condition = $current['condition']['text'];
    $temp_c = $current['temp_c'];
    $feelslike_c = $current['feelslike_c'];
    $wind_kph = $current['wind_kph'];
    $humidity = $current['humidity'];

    $output = '
    <div style="border: 1px solid #ccc; padding: 15px; border-radius: 5px; background: #f9f9f9; max-width: 300px;">
        <h3 style="margin-top: 0;">Current Weather in ' . esc_html($city) . '</h3>
        <p><strong>Condition:</strong> ' . esc_html($condition) . '</p>
        <p><strong>Temperature:</strong> ' . esc_html($temp_c) . '&deg;C</p>
        <p><strong>Feels Like:</strong> ' . esc_html($feelslike_c) . '&deg;C</p>
        <p><strong>Wind:</strong> ' . esc_html($wind_kph) . ' km/h</p>
        <p><strong>Humidity:</strong> ' . esc_html($humidity) . '%</p>
        <small>Last updated: ' . esc_html($current['last_updated']) . '</small>
    </div>';

    // Cache for 10 minutes
    set_transient($transient_key, $output, 600);

    return $output;
}
?>
