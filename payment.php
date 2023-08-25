    <?php 
// Inclui o arquivo de configuração
require_once 'vendor/autoload.php'; // Caminho para o autoloader do Composer
require_once 'config.php'; 

// Obtém o ID do usuário da SESSION atual
$userID = isset($_SESSION['loggedInUserID']) ? $_SESSION['loggedInUserID'] : 4; 

$payment_id = $statusMsg = $api_error = ''; 
$ordStatus = 'error'; 

// Verifica se o token do Stripe e o plano de assinatura não estão vazios
if (!empty($_POST['subscr_plan']) && !empty($_POST['stripeToken'])) { 
     
    // Recupera o token do Stripe e as informações do usuário do formulário enviado
    $token  = $_POST['stripeToken']; 
    $name = $_POST['name']; 
    $email = $_POST['email']; 
     
    // Informações do plano
    $planID = $_POST['subscr_plan']; 
    $planInfo = $plans[$planID]; 
    $planName = $planInfo['name']; 
    $planPrice = $planInfo['price']; 
    $planInterval = $planInfo['interval']; 
     
    // Inclui a biblioteca Stripe PHP 
    require_once 'vendor\stripe\stripe-php/init.php'; 
     
    // Define a chave da API
    \Stripe\Stripe::setApiKey(STRIPE_API_KEY); 
     
    // Adiciona o cliente ao Stripe
    try {  
        $customer = \Stripe\Customer::create(array( 
            'email' => $email, 
            'source'  => $token 
        )); 
    } catch (Exception $e) {  
        $api_error = $e->getMessage();  
    } 
     
    if (empty($api_error) && $customer) {  
     
        // Converte o preço para centavos
        $priceCents = round($planPrice * 100); 
     
        // Cria um plano
        try { 
            $plan = \Stripe\Plan::create(array( 
                "product" => [ 
                    "name" => $planName 
                ], 
                "amount" => $priceCents, 
                "currency" => $currency, 
                "interval" => $planInterval, 
                "interval_count" => 1 
            )); 
        } catch (Exception $e) { 
            $api_error = $e->getMessage(); 
        } 
         
        if (empty($api_error) && $plan) { 
            // Cria uma nova assinatura
            try { 
                $subscription = \Stripe\Subscription::create(array( 
                    "customer" => $customer->id, 
                    "items" => array( 
                        array( 
                            "plan" => $plan->id, 
                        ), 
                    ), 
                )); 
            } catch (Exception $e) { 
                $api_error = $e->getMessage(); 
            } 
             
            if (empty($api_error) && $subscription) { 
                // Recupera os dados da assinatura
                $subsData = $subscription->jsonSerialize(); 
         
                // Verifica se a ativação da assinatura foi bem-sucedida
                if ($subsData['status'] == 'active') { 
                    // Informações da assinatura
                    $subscrID = $subsData['id']; 
                    $custID = $subsData['customer']; 
                    $planID = $subsData['plan']['id']; 
                    $planAmount = ($subsData['plan']['amount'] / 100); 
                    $planCurrency = $subsData['plan']['currency']; 
                    $planinterval = $subsData['plan']['interval']; 
                    $planIntervalCount = $subsData['plan']['interval_count']; 
                    $created = date("Y-m-d H:i:s", $subsData['created']); 
                    $current_period_start = date("Y-m-d H:i:s", $subsData['current_period_start']); 
                    $current_period_end = date("Y-m-d H:i:s", $subsData['current_period_end']); 
                    $status = $subsData['status']; 
                     
                    // Inclui o arquivo de conexão com o banco de dados
                    include_once 'dbConnect.php'; 
         
                    // Insere os dados da transação no banco de dados
                    $sql = "INSERT INTO user_subscriptions(user_id,stripe_subscription_id,stripe_customer_id,stripe_plan_id,plan_amount,plan_amount_currency,plan_interval,plan_interval_count,payer_email,created,plan_period_start,plan_period_end,status) VALUES('".$userID."','".$subscrID."','".$custID."','".$planID."','".$planAmount."','".$planCurrency."','".$planinterval."','".$planIntervalCount."','".$email."','".$created."','".$current_period_start."','".$current_period_end."','".$status."')"; 
                    $insert = $db->query($sql);  
                      
                    // Atualiza o ID da assinatura na tabela de usuários
                    if ($insert && !empty($userID)) {  
                        $subscription_id = $db->insert_id;  
                        $update = $db->query("UPDATE users SET subscription_id = {$subscription_id} WHERE id = {$userID}");  
                    } 
                     
                    $ordStatus = 'success'; 
                    $statusMsg = 'Seu pagamento de assinatura foi bem-sucedido!'; 
                } else { 
                    $statusMsg = "Falha na ativação da assinatura!"; 
                } 
            } else { 
                $statusMsg = "Falha na criação da assinatura! ".$api_error; 
            } 
        } else { 
            $statusMsg = "Falha na criação do plano! ".$api_error; 
        } 
    } else {  
        $statusMsg = "Detalhes do cartão inválidos! $api_error";  
    } 
} else { 
    $statusMsg = "Erro no envio do formulário, por favor, tente novamente."; 
} 
?>

<div class="container">
    <div class="status">
        <h1 class="<?php echo $ordStatus; ?>"><?php echo $statusMsg; ?></h1>
        <?php if (!empty($subscrID)) { ?>
            <h4>Informações de Pagamento</h4>
            <p><b>Número de Referência:</b> <?php echo $subscription_id; ?></p>
            <p><b>ID da Transação:</b> <?php echo $subscrID; ?></p>
            <p><b>Valor:</b> <?php echo $planAmount.' '.$planCurrency; ?></p>
            
            <h4>Informações da Assinatura</h4>
            <p><b>Nome do Plano:</b> <?php echo $planName; ?></p>
            <p><b>Valor:</b> <?php echo $planPrice.' '.$currency; ?></p>
            <p><b>Intervalo do Plano:</b> <?php echo $planInterval; ?></p>
            <p><b>Início do Período:</b> <?php echo $current_period_start; ?></p>
            <p><b>Fim do Período:</b> <?php echo $current_period_end; ?></p>
            <p><b>Status:</b> <?php echo $status; ?></p>
        <?php } ?>
    </div>
    <a href="index.php" class="btn-link">Voltar para a Página de Assinatura</a>
</div>
