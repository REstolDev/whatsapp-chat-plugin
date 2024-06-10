<?php
/*
Plugin Name: WhatsApp Chat
Plugin URI: https://github.com/REstolDev/whatsapp-chat-plugin
Description: Adds a WhatsApp Chat button to the bottom right of the screen.
Version: 1.0.4
Author: Ramon Estol
Author URI: https://ramonestol.com/
License: GPLv2 or later
*/

// Add WhatsApp Chat to the front-end
add_action('wp_footer', 'add_whatsapp_chat');
function add_whatsapp_chat()
{
    $whatsapp_number = get_option('whatsapp_chat_number', '+1234567890');
    $default_message = get_option('whatsapp_chat_message', 'Hi, Flex Up! Can you help me?');
    $whatsapp_icon = get_option('whatsapp_chat_icon', plugin_dir_url(__FILE__) . 'whatsapp-icon.svg');
    ?>
    <a href="https://wa.me/<?php echo esc_attr($whatsapp_number); ?>?text=<?php echo urlencode($default_message); ?>"
        target="_blank" class="whatsapp-button" title="WhatsApp Chat">
        <img src="<?php echo esc_url($whatsapp_icon); ?>" class="whatsapp-icon" alt="WhatsApp">
    </a>
    <?php
}

// Enqueue custom CSS for the front-end
add_action('wp_enqueue_scripts', 'enqueue_whatsapp_chat_styles');
function enqueue_whatsapp_chat_styles()
{
    wp_enqueue_style('whatsapp-chat', plugin_dir_url(__FILE__) . 'whatsapp-chat.css', array(), '1.0');
}

// Enqueue custom CSS and JS for the admin settings page
add_action('admin_enqueue_scripts', 'enqueue_whatsapp_chat_admin_styles');
function enqueue_whatsapp_chat_admin_styles()
{
    wp_enqueue_style('whatsapp-chat-admin', plugin_dir_url(__FILE__) . 'whatsapp-chat-admin.css', array(), '1.0');
    wp_enqueue_media();
    wp_enqueue_script('whatsapp-chat-admin', plugin_dir_url(__FILE__) . 'whatsapp-chat-admin.js', array('jquery'), '1.0', true);
}

// Add options to the WordPress dashboard
add_action('admin_menu', 'add_whatsapp_chat_settings_page');
function add_whatsapp_chat_settings_page()
{
    add_options_page('WhatsApp Chat', 'WhatsApp Chat', 'manage_options', 'whatsapp_chat', 'whatsapp_chat_settings_page');
}

// Add settings link to the plugin list
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'add_whatsapp_chat_settings_link');
function add_whatsapp_chat_settings_link($links)
{
    $settings_link = '<a href="' . admin_url('options-general.php?page=whatsapp_chat') . '">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}

function whatsapp_chat_settings_page()
{
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'whatsapp_chat-settings-nonce')) {
        if (isset($_POST['whatsapp_number'])) {
            $whatsapp_number = sanitize_text_field($_POST['whatsapp_number']);
            update_option('whatsapp_chat_number', $whatsapp_number);
        }

        if (isset($_POST['default_message'])) {
            $default_message = sanitize_text_field($_POST['default_message']);
            update_option('whatsapp_chat_message', $default_message);
        }

        if (isset($_POST['whatsapp_icon'])) {
            $whatsapp_icon = esc_url_raw($_POST['whatsapp_icon']);
            update_option('whatsapp_chat_icon', $whatsapp_icon);
        }
    }

    $whatsapp_number = get_option('whatsapp_chat_number', '+1234567890');
    $default_message = get_option('whatsapp_chat_message', 'Hi there! How can I help you?');
    $whatsapp_icon = get_option('whatsapp_chat_icon', plugin_dir_url(__FILE__) . 'whatsapp-icon.svg');

    ?>
    <div class="wrap whatsapp-chat-settings">
        <h1>WhatsApp Chat Settings</h1>
        <form method="post">
            <?php wp_nonce_field('whatsapp_chat-settings-nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="whatsapp_number">WhatsApp phone number:</label></th>
                    <td><input type="text" id="whatsapp_number" name="whatsapp_number"
                            value="<?php echo esc_attr($whatsapp_number); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="default_message">Default message:</label></th>
                    <td><textarea id="default_message" name="default_message"
                            class="large-text"><?php echo esc_textarea($default_message); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="whatsapp_icon">WhatsApp Icon:</label></th>
                    <td>
                        <input type="text" id="whatsapp_icon" name="whatsapp_icon"
                            value="<?php echo esc_url($whatsapp_icon); ?>" class="regular-text">
                        <button type="button" class="button button-secondary" id="whatsapp_icon_button">Select Icon</button>
                        <div><img src="<?php echo esc_url($whatsapp_icon); ?>" id="whatsapp_icon_preview"
                                style="max-width: 60px; margin-top: 10px;"></div>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" class="button-primary" value="Save Changes">
            </p>
        </form>
    </div>
    <?php
}


// Comprueba actualizaciones al inicio del panel de administración
add_action('admin_init', 'whatsapp_chat_check_for_updates');
function whatsapp_chat_check_for_updates() {
    $current_version = get_option('whatsapp_chat_version');
    $latest_version = whatsapp_chat_get_latest_version_from_github();

    if (!$latest_version) {
        return;
    }

    if (version_compare($latest_version, $current_version, '>')) {
        set_transient('whatsapp_chat_update_available', true, DAY_IN_SECONDS); // Establece el aviso de actualización por 1 día
    } else {
        delete_transient('whatsapp_chat_update_available'); // Elimina el aviso si ya está actualizado
    }
}

// Muestra un aviso de actualización en la página de ajustes del plugin
add_action('admin_notices', 'whatsapp_chat_settings_update_notice');
function whatsapp_chat_settings_update_notice() {
    if (get_transient('whatsapp_chat_update_available')) {
        ?>
        <div class="notice notice-info">
            <p>¡Una nueva versión del plugin WhatsApp Chat está disponible! Por favor, <a href="https://github.com/REstolDev/whatsapp-chat-plugin/archive/main.zip">descarga el plugin aquí</a> y sigue estos pasos para actualizar:</p>
            <ol>
                <li>Descarga el archivo ZIP del plugin desde el enlace anterior.</li>
                <li>Sube el nuevo archivo ZIP descargado e instálalo como un nuevo plugin.</li>
            </ol>
        </div>
        <?php
    }
}

// Obtiene la última versión del plugin desde GitHub
function whatsapp_chat_get_latest_version_from_github() {
    $url = 'https://api.github.com/repos/REstolDev/whatsapp-chat-plugin/tags';

    // Agregar mensaje de depuración
    error_log('WhatsApp Chat: Realizando solicitud a la API de GitHub...');

    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        // Agregar mensaje de depuración
        error_log('WhatsApp Chat: Error en la solicitud a la API de GitHub: ' . $response->get_error_message());
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!empty($data[0]['name'])) {
        // Agregar mensaje de depuración
        error_log('WhatsApp Chat: Última versión obtenida desde GitHub: ' . $data[0]['name']);
        return $data[0]['name'];
    }

    // Agregar mensaje de depuración
    error_log('WhatsApp Chat: No se pudo obtener la última versión desde GitHub.');
    return false;
}

// Almacena la versión actual del plugin
function whatsapp_chat_activate_plugin() {
    $current_version = get_option('whatsapp_chat_version');
    $latest_version = whatsapp_chat_get_latest_version_from_github();

    if (!$current_version || version_compare($latest_version, $current_version, '>')) {
        update_option('whatsapp_chat_version', $latest_version);
    }
}
register_activation_hook(__FILE__, 'whatsapp_chat_activate_plugin');

// Actualiza la versión del plugin después de actualizar
add_action('upgrader_process_complete', 'whatsapp_chat_after_update', 10, 2);
function whatsapp_chat_after_update($upgrader_object, $options) {
    if ($options['action'] == 'update' && $options['type'] == 'plugin' ) {
        if (isset($options['plugins']) && in_array(plugin_basename(__FILE__), $options['plugins'])) {
            $latest_version = whatsapp_chat_get_latest_version_from_github();
            update_option('whatsapp_chat_version', $latest_version);
            delete_transient('whatsapp_chat_update_available');
        }
    }
}
