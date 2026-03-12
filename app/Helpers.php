<?php
if (!function_exists('getSemaforoClass')) {
    function getSemaforoClass($estado) {
        switch ($estado) {
            case 'Excedido': return 'bg-primary';   // Azul
            case 'Aceptable': return 'bg-success';  // Verde
            case 'Moderado': return 'bg-warning';   // Amarillo
            case 'Insuficiente': return 'bg-danger'; // Rojo
            default: return 'bg-secondary';         // Gris (No clasificado)
        }
    }
}
