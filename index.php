<?php 
// Inclui o arquivo de configuração
require_once 'config.php'; 
?>

<div class="panel">
    <form action="payment.php" method="POST" id="paymentFrm">
        <div class="panel-heading">
            <h3 class="panel-title">Assinatura de Plano com o Stripe</h3>
			
            <!-- Informações do Plano -->
            <p>
                <b>Selecione o Plano:</b>
                <select name="subscr_plan" id="subscr_plan">
                    <?php foreach($plans as $id=>$plan){ ?>
                        <option value="<?php echo $id; ?>"><?php echo $plan['name'].' [R$ '.$plan['price'].'/'.$plan['interval'].']'; ?></option>
                    <?php } ?>
                </select>
            </p>
        </div>
        <div class="panel-body">
            <!-- Exibe os erros retornados por createToken -->
            <div id="paymentResponse"></div>
			
            <!-- Formulário de Pagamento -->
            <div class="form-group">
                <label>NOME</label>
                <input type="text" name="name" id="name" class="field" placeholder="Digite o nome" required="" autofocus="">
            </div>
            <div class="form-group">
                <label>E-MAIL</label>
                <input type="email" name="email" id="email" class="field" placeholder="Digite o e-mail" required="">
            </div>
            <div class="form-group">
                <label>NÚMERO DO CARTÃO</label>
                <div id="card_number" class="field"></div>
            </div>
            <div class="row">
                <div class="left">
                    <div class="form-group">
                        <label>VENCIMENTO</label>
                        <div id="card_expiry" class="field"></div>
                    </div>
                </div>
                <div class="right">
                    <div class="form-group">
                        <label>CVC</label>
                        <div id="card_cvc" class="field"></div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success" id="payBtn">Enviar Pagamento</button>
        </div>
    </form>
</div>

<script src="https://js.stripe.com/v3/"></script>

<script>
// Cria uma instância do objeto Stripe
// Define sua chave de API pública
var stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');

// Cria uma instância dos elementos do Stripe
var elements = stripe.elements();

var style = {
    base: {
        fontWeight: 400,
        fontFamily: 'Roboto, Open Sans, Segoe UI, sans-serif',
        fontSize: '16px',
        lineHeight: '1.4',
        color: '#555',
        backgroundColor: '#fff',
        '::placeholder': {
            color: '#888',
        },
    },
    invalid: {
        color: '#eb1c26',
    }
};

var cardElement = elements.create('cardNumber', {
    style: style
});
cardElement.mount('#card_number');

var exp = elements.create('cardExpiry', {
    'style': style
});
exp.mount('#card_expiry');

var cvc = elements.create('cardCvc', {
    'style': style
});
cvc.mount('#card_cvc');

// Valida a entrada dos elementos do cartão
var resultContainer = document.getElementById('paymentResponse');
cardElement.addEventListener('change', function(event) {
    if (event.error) {
        resultContainer.innerHTML = '<p>'+event.error.message+'</p>';
    } else {
        resultContainer.innerHTML = '';
    }
});

// Obtém o elemento do formulário de pagamento
var form = document.getElementById('paymentFrm');

// Cria um token quando o formulário for enviado.
form.addEventListener('submit', function(e) {
    e.preventDefault();
    createToken();
});

// Cria um token de uso único para cobrar o usuário
function createToken() {
    stripe.createToken(cardElement).then(function(result) {
        if (result.error) {
            // Informa o usuário se houve um erro
            resultContainer.innerHTML = '<p>'+result.error.message+'</p>';
        } else {
            // Envia o token para o seu servidor
            stripeTokenHandler(result.token);
        }
    });
}

// Callback para lidar com a resposta do Stripe
function stripeTokenHandler(token) {
    // Insere o ID do token no formulário para que ele seja enviado ao servidor
    var hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'stripeToken');
    hiddenInput.setAttribute('value', token.id);
    form.appendChild(hiddenInput);
	
    // Envia o formulário
    form.submit();
}
</script>
