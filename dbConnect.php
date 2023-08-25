<?php  
// Conecta com o banco de dados  
$db = new mysqli('localhost', 'root', '', 'banco');  

// Exibe um erro se a conexão falhar  
if ($db->connect_errno) {  
    printf("Falha na conexão: %s\n", $db->connect_error);  
    exit();  
}
