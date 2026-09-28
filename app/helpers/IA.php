<?php
class IA {
    public static function generarCopy(array $pub): string {
        $apiKey   = getenv('IA_API_KEY');
        $endpoint = getenv('IA_ENDPOINT') ?: 'https://api.openai.com/v1/chat/completions';
        $modelo   = getenv('IA_MODEL') ?: 'gpt-4o-mini';

        if (!$apiKey) {
            throw new RuntimeException('Falta configurar IA_API_KEY en .env');
        }

        $prompt = "Eres community manager de la Red de Transporte de Pasajeros de la Ciudad de México (RTP).\n"
                . "Redacta un copy para redes sociales con:\n"
                . "- Título: " . ($pub['titulo'] ?? '') . "\n"
                . "- Campaña: " . ($pub['campana_nombre'] ?? '') . "\n"
                . "- Tema: " . ($pub['tema_nombre'] ?? '') . "\n"
                . "Tono cercano, institucional, incluyente. Máximo 280 caracteres + 3 hashtags + emojis.\n"
                . "Devuelve solo el texto del copy.";

        $payload = [
            'model'    => $modelo,
            'messages' => [
                ['role' => 'system', 'content' => 'Eres redactor institucional de RTP CDMX.'],
                ['role' => 'user',   'content' => $prompt],
            ],
            'temperature' => 0.7,
            'max_tokens'  => 300,
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT    => 30,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200) {
            throw new RuntimeException("Error IA ($code): $resp");
        }
        $json  = json_decode($resp, true);
        $texto = trim($json['choices'][0]['message']['content'] ?? '');

        // Log en BD
        $st = db()->prepare("INSERT INTO ia_logs (usuario_id, prompt, respuesta) VALUES (?, ?, ?)");
        $st->execute([$_SESSION['usuario_id'] ?? null, $prompt, $texto]);

        return $texto;
    }
}