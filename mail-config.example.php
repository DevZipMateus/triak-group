<?php
// Copie este arquivo para "mail-config.php" e preencha com os dados reais.
// mail-config.php NÃO deve ser commitado no git (já está no .gitignore).

return [
    // Conta usada apenas para autenticar e ENVIAR o e-mail (relay).
    'smtp_host' => 'smtp.hostinger.com',
    'smtp_port' => 465,
    'smtp_secure' => 'ssl', // 465 = ssl, 587 = tls
    'smtp_user' => 'naoresponda@montesite.com.br',
    'smtp_pass' => 'COLOQUE_A_SENHA_AQUI',

    // Para onde o lead do formulário é encaminhado.
    'mail_to' => 'contato@triakgroup.com.br',
    'mail_to_name' => 'Triak Group',
];
