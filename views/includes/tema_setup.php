<?php
function aplicarTema() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    require_once "../../config/Database.php";
    require_once "../../config/AppConfig.php";
    
    $db = (new Database())->getConnection();
    $config = (new AppConfig($db))->obtenerConfig();
    
    $tema = $_SESSION['tema'] ?? $config['tema'] ?? 'default';
    
    $clase_tema = ($tema !== 'default') ? 'tema-' . $tema : '';
    
    $bg_color = '#f4f4f4';
    $text_color = '#333333';
    $card_bg = '#ffffff';
    $border_color = '#dddddd';
    $header_bg = '#2c3e50';
    $input_bg = '#ffffff';
    $input_text = '#333333';
    $input_border = '#cccccc';
    
    if ($tema === 'oscuro') {
        $bg_color = '#121212';
        $text_color = '#e0e0e0';
        $card_bg = '#1e1e1e';
        $border_color = '#333333';
        $header_bg = '#1a1a2e';
        $input_bg = '#2a2a2a';
        $input_text = '#e0e0e0';
        $input_border = '#444444';
    } elseif ($tema === 'darkblue') {
        $bg_color = '#0d1b2a';
        $text_color = '#e0e0e0';
        $card_bg = '#1b263b';
        $border_color = '#334155';
        $header_bg = '#0d1b2a';
        $input_bg = '#0d1b2a';
        $input_text = '#e0e0e0';
        $input_border = '#334155';
    }
    
    return [
        'clase' => $clase_tema,
        'tema' => $tema,
        'bg_color' => $bg_color,
        'text_color' => $text_color,
        'card_bg' => $card_bg,
        'border_color' => $border_color,
        'header_bg' => $header_bg,
        'input_bg' => $input_bg,
        'input_text' => $input_text,
        'input_border' => $input_border
    ];
}
