<?php

namespace CloudflareCacheMonitor;

class CacheMonitor
{
    private static $instance = null;

    private $worker_url = 'https://this-worker-endpoint.workers.dev';
    private $api_key = 'THIS_KEY_SECRET';

    private function __construct()
    {
        // Obter as opções salvas
        $options = get_option('codenz_ccm_options');
        $this->worker_url = $options['worker_url'] ?? $this->worker_url;
        $this->api_key = $options['api_key'] ?? $this->api_key;

        // Registra o hook
        add_filter('cloudflare_purge_by_url', [$this, 'handlePurgeByUrl'], 10, 2);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new CacheMonitor();
        }
        return self::$instance;
    }

    public function handlePurgeByUrl($urls, $post_id)
    {
        // Obter o timestamp atual
        $purge_time = time();

        // Dados a serem enviados para o Worker
        $data = [
            'post_id' => $post_id,
            'purge_time' => $purge_time,
            'urls' => $urls,
        ];

        // Configurar a solicitação HTTP
        $args = [
            'body'        => json_encode($data),
            'headers'     => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
            ],
            'timeout'     => 15,
            'redirection' => 5,
            'blocking'    => true,
        ];

        // Enviar a solicitação POST para o Worker
        $response = wp_remote_post($this->worker_url, $args);

        // Verificar se houve erro
        if (is_wp_error($response)) {
            error_log('Erro ao enviar dados para o Cloudflare Worker: ' . $response->get_error_message());
        } else {
            error_log('Dados enviados para o Cloudflare Worker com sucesso.');
            // Opcional: Registrar a resposta do Worker
            $response_body = wp_remote_retrieve_body($response);
            error_log('Resposta do Worker: ' . $response_body);
        }

        return $urls;
    }
}
