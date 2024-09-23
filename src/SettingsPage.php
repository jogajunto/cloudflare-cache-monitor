<?php

namespace CloudflareCacheMonitor;

class SettingsPage
{
    private static $instance = null;

    private function __construct()
    {
        // Hook para adicionar a página de configurações
        add_action('admin_menu', [$this, 'addSettingsPage']);
        // Hook para registrar as configurações
        add_action('admin_init', [$this, 'registerSettings']);
        // Hook para interceptar a validação e salvamento dos dados
        add_filter('pre_update_option_codenz_ccm_options', [$this, 'preUpdateOptions'], 10, 2);
    }

    /**
     * Método para obter a instância única da classe (Singleton)
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Adiciona a página de configurações
     */
    public function addSettingsPage()
    {
        add_options_page(
            'Cloudflare Cache Monitor',
            'Cloudflare Cache Monitor',
            'manage_options',
            'codenz-ccm',
            [$this, 'renderSettingsPage']
        );
    }

    /**
     * Renderiza a página de configurações
     */
    public function renderSettingsPage()
    {
?>
        <div class="wrap">
            <h1>Cloudflare Cache Monitor Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('codenz_ccm_options_group');
                do_settings_sections('codenz_ccm');
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    /**
     * Registra as configurações
     */
    public function registerSettings()
    {
        register_setting('codenz_ccm_options_group', 'codenz_ccm_options');

        add_settings_section(
            'codenz_ccm_main_section',
            'Configurações Principais',
            null,
            'codenz_ccm'
        );

        add_settings_field(
            'worker_url',
            'Worker URL',
            [$this, 'workerUrlCallback'],
            'codenz_ccm',
            'codenz_ccm_main_section'
        );

        add_settings_field(
            'api_key',
            'API Key',
            [$this, 'apiKeyCallback'],
            'codenz_ccm',
            'codenz_ccm_main_section'
        );
    }

    /**
     * Callback para o campo worker_url
     */
    public function workerUrlCallback()
    {
        $options = get_option('codenz_ccm_options');
    ?>
        <input type="text" name="codenz_ccm_options[worker_url]" value="<?php echo esc_attr($options['worker_url'] ?? ''); ?>" size="50">
    <?php
    }

    /**
     * Callback para o campo api_key
     */
    public function apiKeyCallback()
    {
        $options = get_option('codenz_ccm_options');
        $apiKey = $options['api_key'] ?? '';

        // Mascarar a API Key para exibição
        if (!empty($apiKey)) {
            $apiKeyMascarada = $this->tokenMasked($apiKey);
        } else {
            $apiKeyMascarada = '';
        }
    ?>
        <input type="text" name="codenz_ccm_options[api_key]" value="<?php echo esc_attr($apiKeyMascarada); ?>" size="50">
<?php
    }

    /**
     * Função para mascarar o token de API
     */
    private function tokenMasked($token, $mascaraChar = '*', $visibleStart = 4, $visibleEnd = 4)
    {
        $tokenLength = strlen($token);

        // Certifique-se de que o token é longo o suficiente para ser mascarado
        if ($tokenLength <= ($visibleStart + $visibleEnd)) {
            return $token; // Retorna o token original se ele for muito curto
        }

        // Define as partes visíveis no início e no final
        $start = substr($token, 0, $visibleStart);
        $end = substr($token, -$visibleEnd);

        // Calcula quantos caracteres serão mascarados
        $mascararParte = str_repeat($mascaraChar, $tokenLength - $visibleStart - $visibleEnd);

        // Concatena a parte visível com a parte mascarada e o final visível
        return $start . $mascararParte . $end;
    }

    /**
     * Hook para manipular o salvamento das opções e garantir que o token original seja salvo
     */
    public function preUpdateOptions($new_value, $old_value)
    {
        // Verifique se a chave de API foi modificada pelo usuário
        if (isset($new_value['api_key']) && isset($old_value['api_key'])) {
            $newApiKey = $new_value['api_key'];
            $originalApiKey = $old_value['api_key'];
            $maskedOriginalKey = $this->tokenMasked($originalApiKey);

            // Verifique se a nova chave de API contém caracteres válidos
            if ($this->isValidToken($newApiKey)) {
                // Se a nova chave for igual à mascarada ou vazia, mantenha o valor original
                if ($newApiKey === $maskedOriginalKey || empty($newApiKey)) {
                    $new_value['api_key'] = $originalApiKey; // Mantém o token original
                }
            } else {
                // Se o token contiver caracteres inválidos, mantenha o valor original
                $new_value['api_key'] = $originalApiKey;
                add_settings_error('codenz_ccm_options', 'invalid_api_key', 'O token de API contém caracteres inválidos e foi mantido o valor original.');
            }
        }

        return $new_value;
    }

    /**
     * Valida o token para garantir que ele contém apenas caracteres permitidos
     * Permitimos apenas letras, números, hífens e underscores por exemplo
     */
    private function isValidToken($token)
    {
        // Expressão regular que permite apenas letras, números, hífens e underscores
        return preg_match('/^[a-zA-Z0-9-_]+$/', $token);
    }
}
