<?php 
// Planos de assinatura 
// O valor mínimo é $0.50 dólares americanos 
// Intervalos: dia, semana, mês ou ano 
$plans = array( 
    '1' => array( 
        'name' => 'Assinatura Semanal', 
        'price' => 25, 
        'interval' => 'week' 
    ), 
    '2' => array( 
        'name' => 'Assinatura Mensal', 
        'price' => 85, 
        'interval' => 'month' 
    ), 
    '3' => array( 
        'name' => 'Assinatura Anual', 
        'price' => 950, 
        'interval' => 'year' 
    ) 
); 
$currency = "BRL";  
 
/* Configuração da API do Stripe 
 * Lembre-se de mudar para suas chaves pública e secreta reais em produção! 
 * Veja suas chaves aqui: https://dashboard.stripe.com/account/apikeys 
 */ 
define('STRIPE_API_KEY', 'sk_test_51Na6XKIzO04zR2uSiQdfMf7jaan9LzsW8WLoblDf7XsuPcPWO9wtiWkZmksF8NmugEQcWX9aNqcV0PsUeoRyr6fG00DNqJF0IB'); 
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_51Na6XKIzO04zR2uSW5XQGfbo8u1Tzpyj15euMDOVf1OET9CSUq3bJ5J3M7DXstVqkX1CuiNzWN9ZzLUrkkJa1cHr00Xre273XB'); 
  
// Configuração do banco de dados  
define('DB_HOST', 'localhost'); 
define('DB_USERNAME', 'root'); 
define('DB_PASSWORD', ''); 
define('DB_NAME', 'banco');
