<?php
// MeuDia+ | Painel principal — acesso somente para usuários autenticados
require "sessao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: inicial.html");
    exit;
}

require "conexao.php";
$dadosUsuario = [];

try {
    $stmtDados = $conexao->prepare(
        "SELECT chave, valor FROM dados_usuario WHERE usuario_id = :usuario_id"
    );
    $stmtDados->execute([':usuario_id' => (int) $_SESSION['usuario_id']]);
    while ($linha = $stmtDados->fetch()) {
        $dadosUsuario[$linha['chave']] = json_decode($linha['valor'], true);
    }
    $conexao = null;
} catch (PDOException $e) {
    error_log("MeuDia+ | Erro ao carregar dados do usuário: " . $e->getMessage());
    http_response_code(500);
    die("Não foi possível carregar seus dados agora. Tente novamente mais tarde.");
}

$vCss = filemtime(__DIR__ . '/painel.css');
$vJs = filemtime(__DIR__ . '/painel.js');
$flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MeuDia+ | Organize sua vida</title>
<link rel="stylesheet" href="painel.css?v=<?= $vCss ?>">
<script>
window.MEUDIA = {
    dados: <?= json_encode($dadosUsuario ?: new stdClass(), $flags) ?>,
    nomeLogin: <?= json_encode($_SESSION['usuario_nome'], $flags) ?>
};
</script>
</head>

<body>
<script>
(function(){
    var t = window.MEUDIA.dados.tema;
    var escuro = t ? t === "escuro"
        : (window.matchMedia && matchMedia("(prefers-color-scheme: dark)").matches);
    if (escuro) document.body.classList.add("dark");
})();
</script>
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false"><symbol id="i-home" viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/></symbol><symbol id="i-tarefas" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="m8 12 3 3 5-6"/></symbol><symbol id="i-agenda" viewBox="0 0 24 24"><rect x="3" y="4.5" width="18" height="16.5" rx="3"/><path d="M3 10h18M8 2.5v4M16 2.5v4"/></symbol><symbol id="i-notas" viewBox="0 0 24 24"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></symbol><symbol id="i-financas" viewBox="0 0 24 24"><path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/></symbol><symbol id="i-compras" viewBox="0 0 24 24"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.7 12.4a1.5 1.5 0 0 0 1.5 1.1h8.6a1.5 1.5 0 0 0 1.5-1.1L21 7H6"/></symbol><symbol id="i-estudos" viewBox="0 0 24 24"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></symbol><symbol id="i-conta" viewBox="0 0 24 24"><path d="M4 6h8M16 6h4M4 12h4M12 12h8M4 18h10M18 18h2"/><circle cx="14" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="16" cy="18" r="2"/></symbol><symbol id="i-sair" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/></symbol><symbol id="i-policia" viewBox="0 0 24 24"><path d="M12 3 4 6v6c0 5 3.4 8.2 8 9.5 4.6-1.3 8-4.5 8-9.5V6z"/></symbol><symbol id="i-samu" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></symbol><symbol id="i-bombeiros" viewBox="0 0 24 24"><path d="M12 3c1 3.5 5 5.5 5 10a5 5 0 0 1-10 0c0-2 1-3.2 2-4.4.3 1 .8 1.7 1.5 2C10.2 8 10.5 5.5 12 3z"/></symbol><symbol id="i-rodovia" viewBox="0 0 24 24"><path d="M5 16l1.5-5.5A2 2 0 0 1 8.4 9h7.2a2 2 0 0 1 1.9 1.5L19 16"/><rect x="3" y="16" width="18" height="4" rx="1.5"/><path d="M7 18h.01M17 18h.01"/></symbol><symbol id="i-defesa" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.5"/><path d="m5.6 5.6 3.9 3.9M14.5 14.5l3.9 3.9M18.4 5.6l-3.9 3.9M9.5 14.5l-3.9 3.9"/></symbol><symbol id="i-denuncia" viewBox="0 0 24 24"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></symbol></svg>

<header>
    <div class="logo">
        <img src="logo.png" alt="Logo MeuDia+" style="height:45px;width:auto;">
        <div>
            <h1>MeuDia+</h1>
            <p>Organize sua vida em um só lugar</p>
        </div>
    </div>

    <nav>
        <button class="active" onclick="abrirPagina('inicio',this)"><svg class="ico" aria-hidden="true"><use href="#i-home"/></svg>Início</button>
        <button onclick="abrirPagina('tarefas',this)"><svg class="ico" aria-hidden="true"><use href="#i-tarefas"/></svg>Tarefas</button>
        <button onclick="abrirPagina('agenda',this)"><svg class="ico" aria-hidden="true"><use href="#i-agenda"/></svg>Agenda</button>
        <button onclick="abrirPagina('notas',this)"><svg class="ico" aria-hidden="true"><use href="#i-notas"/></svg>Notas</button>
        <button onclick="abrirPagina('financas',this)"><svg class="ico" aria-hidden="true"><use href="#i-financas"/></svg>Finanças</button>
        <button onclick="abrirPagina('compras',this)"><svg class="ico" aria-hidden="true"><use href="#i-compras"/></svg>Compras</button>
        <button onclick="abrirPagina('estudos',this)"><svg class="ico" aria-hidden="true"><use href="#i-estudos"/></svg>Estudos</button>
        <button onclick="abrirPagina('conta',this)"><svg class="ico" aria-hidden="true"><use href="#i-conta"/></svg>Conta</button>
        <button onclick="window.location.href='logout.php'"><svg class="ico" aria-hidden="true"><use href="#i-sair"/></svg>Sair</button>
        <div class="nav-tema">
            <label for="switchMenu">Modo escuro</label>
            <button type="button" id="switchMenu" class="switch" role="switch" aria-checked="false" onclick="alternarTema()"><span class="switch-texto"></span><span class="switch-bola"></span></button>
        </div>
    </nav>
</header>

<div class="container">

<!-- INÍCIO -->
<section id="inicio" class="page active">

    <div class="hero">
        <h2 id="saudacao">Olá!</h2>

        <p>
            Tenha suas principais atividades, compromissos,
            anotações e finanças organizados em um só lugar.
        </p>

        <div class="data-atual" id="dataAtual"></div>

        <div class="search">
            <input id="buscaGeral"
                   placeholder="Pesquise tarefas, notas, compras...">
            <button onclick="buscarTudo()">Pesquisar</button>
        </div>
    </div>

    <div class="grid">

        <div class="card">
            <div class="card-icon"><svg class="ico" aria-hidden="true"><use href="#i-tarefas"/></svg></div>
            <h3>Tarefas</h3>
            <p id="totalTarefas" class="stat">0</p>
            <p>tarefas cadastradas</p>
        </div>

        <div class="card">
            <div class="card-icon"><svg class="ico" aria-hidden="true"><use href="#i-agenda"/></svg></div>
            <h3>Agenda</h3>
            <p id="totalAgenda" class="stat">0</p>
            <p>compromissos</p>
        </div>

        <div class="card">
            <div class="card-icon"><svg class="ico" aria-hidden="true"><use href="#i-compras"/></svg></div>
            <h3>Compras</h3>
            <p id="totalCompras" class="stat">0</p>
            <p>itens na lista</p>
        </div>

        <div class="card">
            <div class="card-icon"><svg class="ico" aria-hidden="true"><use href="#i-notas"/></svg></div>
            <h3>Anotações</h3>
            <p id="totalNotas" class="stat">0</p>
            <p>anotações salvas</p>
        </div>

    </div>

    <div class="dashboard-grid">

        <div class="box">
            <h2>Próximas tarefas</h2>
            <div id="resumoTarefas"></div>
        </div>

        <div class="box">
            <h2>Resumo financeiro</h2>

            <p>Renda:</p>
            <strong id="dashRenda">R$ 0,00</strong>

            <br><br>

            <p>Gastos:</p>
            <strong id="dashGastos">R$ 0,00</strong>

            <br><br>

            <p>Saldo:</p>
            <strong id="dashSaldo">R$ 0,00</strong>
        </div>

    </div>

</section>

<!-- TAREFAS -->
<section id="tarefas" class="page">

    <div class="titulo">
        <h2>Minhas tarefas</h2>
        <p>Organize tudo o que precisa fazer.</p>
    </div>

    <div class="box">
        <div class="form-grid">

            <div>
                <label>Tarefa</label>
                <textarea id="tarefaTexto" class="tarefa-texto"
                          placeholder="Ex.: Fazer trabalho da escola (use Enter para pular de linha)"></textarea>
            </div>

            <div>
                <label>Prioridade</label>
                <select id="tarefaPrioridade">
                    <option>Normal</option>
                    <option>Importante</option>
                    <option>Urgente</option>
                </select>
            </div>
            <div>
                <label>Prazo (opcional)</label>
                <input id="tarefaPrazo" type="date">
            </div>

        </div>

        <button class="btn"
                style="margin-top:15px"
                onclick="adicionarTarefa()">
            Adicionar tarefa
        </button>
    </div>

    <div class="box">
        <h2>Lista</h2>
        <div id="listaTarefas"></div>
    </div>

</section>

<!-- AGENDA -->
<section id="agenda" class="page">

    <div class="titulo">
        <h2>Minha agenda</h2>
        <p>Cadastre seus compromissos.</p>
    </div>

    <div class="box">
        <div class="form-grid">

            <div>
                <label>Compromisso</label>
                <input id="eventoNome"
                       placeholder="Ex.: Consulta médica">
            </div>

            <div>
                <label>Data</label>
                <input id="eventoData" type="date">
            </div>

            <div>
                <label>Horário</label>
                <input id="eventoHora" type="time">
            </div>

            <div>
                <label>Local</label>
                <input id="eventoLocal"
                       placeholder="Ex.: Centro">
            </div>

        </div>

        <button class="btn"
                style="margin-top:15px"
                onclick="adicionarEvento()">
            Adicionar compromisso
        </button>
    </div>

    <div class="box">
        <h2>Meus compromissos</h2>
        <div id="listaAgenda"></div>
    </div>

</section>

<!-- NOTAS -->
<section id="notas" class="page">

    <div class="titulo">
        <h2>Minhas anotações</h2>
        <p>Guarde ideias, informações e lembretes.</p>
    </div>

    <div class="box">

        <label>Título</label>
        <input id="notaTitulo"
               placeholder="Título da anotação">

        <br><br>

        <label>Texto</label>
        <textarea id="notaTexto"
                  placeholder="Escreva aqui..."></textarea>

        <button class="btn"
                style="margin-top:15px"
                onclick="adicionarNota()">
            Salvar anotação
        </button>

    </div>

    <div class="box">
        <h2>Anotações salvas</h2>
        <div id="listaNotas"></div>
    </div>

</section>

<!-- FINANÇAS -->
<section id="financas" class="page">

    <div class="titulo">
        <h2>Minhas finanças</h2>
        <p>Controle sua renda e seus gastos.</p>
    </div>

    <div class="finance-grid">

        <div class="finance-card">
            <h3>Renda</h3>
            <div id="rendaValor" class="finance-value entrada">
                R$ 0,00
            </div>
        </div>

        <div class="finance-card">
            <h3>Gastos</h3>
            <div id="gastosValor" class="finance-value saida">
                R$ 0,00
            </div>
        </div>

        <div class="finance-card">
            <h3>Saldo</h3>
            <div id="saldoValor" class="finance-value saldo">
                R$ 0,00
            </div>
        </div>

    </div>

    <div class="box" style="margin-top:20px">

        <h2>Minha renda</h2>

        <div class="form-grid">

            <div>
                <label>Valor da renda</label>
                <input id="rendaInput"
                       type="number"
                       step="0.01"
                       placeholder="Ex.: 2500">
            </div>

            <div>
                <label>Fonte</label>
                <input id="rendaFonte"
                       placeholder="Ex.: Salário">
            </div>

        </div>

        <button class="btn"
                style="margin-top:15px"
                onclick="salvarRenda()">
            Salvar renda
        </button>

    </div>

    <div class="box">

        <h2>Adicionar gasto</h2>

        <div class="form-grid">

            <div>
                <label>Descrição</label>
                <input id="gastoDescricao"
                       placeholder="Ex.: Mercado">
            </div>

            <div>
                <label>Valor</label>
                <input id="gastoValor"
                       type="number"
                       step="0.01"
                       placeholder="Ex.: 100">
            </div>

            <div>
                <label>Categoria</label>
                <select id="gastoCategoria">
                    <option>Alimentação</option>
                    <option>Transporte</option>
                    <option>Casa</option>
                    <option>Estudos</option>
                    <option>Lazer</option>
                    <option>Compras</option>
                    <option>Outros</option>
                </select>
            </div>

        </div>

        <button class="btn"
                style="margin-top:15px"
                onclick="adicionarGasto()">
            Adicionar gasto
        </button>

    </div>

    <div class="box">
        <h2>Gastos por categoria</h2>
        <div id="graficoGastos"></div>
    </div>

    <div class="box">
        <h2>Histórico de gastos</h2>
        <div id="listaGastos"></div>
    </div>

</section>

<!-- COMPRAS -->
<section id="compras" class="page">

    <div class="titulo">
        <h2>Lista de compras</h2>
        <p>Não esqueça o que precisa comprar.</p>
    </div>

    <div class="box">
        <div class="search">
            <input id="produtoInput"
                   placeholder="Ex.: Arroz, leite, pão...">
            <button onclick="adicionarCompra()">Adicionar</button>
        </div>
    </div>

    <div class="box">
        <h2>Minha lista</h2>
        <div id="listaCompras"></div>
    </div>

</section>

<!-- ESTUDOS -->
<section id="estudos" class="page">

    <div class="titulo">
        <h2>Estudos</h2>
        <p>Organize suas matérias e atividades escolares.</p>
    </div>

    <div class="box">

        <div class="form-grid">

            <div>
                <label>Matéria</label>
                <input id="materia"
                       placeholder="Ex.: Matemática">
            </div>

            <div>
                <label>Atividade</label>
                <input id="atividade"
                       placeholder="Ex.: Fazer exercícios">
            </div>

            <div>
                <label>Data de entrega</label>
                <input id="dataEstudo" type="date">
            </div>

        </div>

        <button class="btn"
                style="margin-top:15px"
                onclick="adicionarEstudo()">
            Adicionar atividade
        </button>

    </div>

    <div class="box">
        <h2>Atividades</h2>
        <div id="listaEstudos"></div>
    </div>

</section>

<!-- TRABALHO -->
<section id="trabalho" class="page">

    <div class="titulo">
        <h2>Trabalho</h2>
        <p>Organize suas atividades profissionais.</p>
    </div>

    <div class="box">

        <div class="form-grid">

            <div>
                <label>Tarefa profissional</label>
                <input id="trabalhoTexto"
                       placeholder="Ex.: Entregar relatório">
            </div>

            <div>
                <label>Prazo</label>
                <input id="trabalhoData" type="date">
            </div>

            <div class="full">
                <label>Observação</label>
                <textarea id="trabalhoObs"
                          placeholder="Informações adicionais..."></textarea>
            </div>

        </div>

        <button class="btn"
                style="margin-top:15px"
                onclick="adicionarTrabalho()">
            Adicionar atividade
        </button>

    </div>

    <div class="box">
        <h2>Atividades profissionais</h2>
        <div id="listaTrabalho"></div>
    </div>

</section>

<!-- EMERGÊNCIA -->
<section id="emergencia" class="page">

    <div class="alerta">
        <h2>Emergência</h2>
        <p>
            Em uma situação de emergência, procure um local seguro
            e entre em contato com o serviço adequado.
        </p>
    </div>

    <div class="emergencias">

        <div class="emergencia">
            <div class="emergencia-icon"><svg class="ico" aria-hidden="true"><use href="#i-policia"/></svg></div>
            <h3>Polícia</h3>
            <p>Emergências policiais.</p>
            <a class="ligar" href="tel:190">Ligar 190</a>
        </div>

        <div class="emergencia">
            <div class="emergencia-icon"><svg class="ico" aria-hidden="true"><use href="#i-samu"/></svg></div>
            <h3>SAMU</h3>
            <p>Atendimento médico de urgência.</p>
            <a class="ligar" href="tel:192">Ligar 192</a>
        </div>

        <div class="emergencia">
            <div class="emergencia-icon"><svg class="ico" aria-hidden="true"><use href="#i-bombeiros"/></svg></div>
            <h3>Bombeiros</h3>
            <p>Incêndios e situações de emergência.</p>
            <a class="ligar" href="tel:193">Ligar 193</a>
        </div>

        <div class="emergencia">
            <div class="emergencia-icon"><svg class="ico" aria-hidden="true"><use href="#i-rodovia"/></svg></div>
            <h3>PRF</h3>
            <p>Emergências em rodovias federais.</p>
            <a class="ligar" href="tel:191">Ligar 191</a>
        </div>

        <div class="emergencia">
            <div class="emergencia-icon"><svg class="ico" aria-hidden="true"><use href="#i-defesa"/></svg></div>
            <h3>Defesa Civil</h3>
            <p>Desastres e situações de risco.</p>
            <a class="ligar" href="tel:199">Ligar 199</a>
        </div>

        <div class="emergencia">
            <div class="emergencia-icon"><svg class="ico" aria-hidden="true"><use href="#i-denuncia"/></svg></div>
            <h3>Disque-Denúncia</h3>
            <p>Procure o serviço disponível na sua região.</p>
        </div>

    </div>

</section>

<!-- PERFIL -->
<section id="perfil" class="page">

    <div class="titulo">
        <h2>Meu perfil</h2>
        <p>Configure suas informações.</p>
    </div>

    <div class="box">

        <div class="form-grid">

            <div>
                <label>Nome</label>
                <input id="perfilNome"
                       placeholder="Seu nome">
            </div>

            <div>
                <label>Cidade</label>
                <input id="perfilCidade"
                       placeholder="Sua cidade">
            </div>

            <div>
                <label>Profissão</label>
                <input id="perfilProfissao"
                       placeholder="Sua profissão">
            </div>

            <div>
                <label>Objetivo</label>
                <input id="perfilObjetivo"
                       placeholder="Ex.: Organizar minha rotina">
            </div>

            <div class="full">
                <label>Sobre você</label>
                <textarea id="perfilSobre"
                          placeholder="Escreva algumas informações..."></textarea>
            </div>

        </div>

        <button class="btn"
                style="margin-top:15px"
                onclick="salvarPerfil()">
            Salvar perfil
        </button>

    </div>

</section>

<!-- RESULTADOS -->
<!-- CONTA -->
<section id="conta" class="page">

    <div class="titulo">
        <h2>Minha conta</h2>
        <p>Aparência, segurança e seus dados.</p>
    </div>

    <div class="box">
        <h2>Aparência</h2>
        <p class="descricao">Ligue o modo escuro para deixar o painel com fundo escuro.</p>
        <div class="linha-switch">
            <button type="button" class="switch grande" role="switch" aria-checked="false" aria-label="Modo escuro" onclick="alternarTema()"><span class="switch-texto"></span><span class="switch-bola"></span></button>
            <strong>Modo escuro</strong>
        </div>
    </div>

    <div class="box">
        <h2>Alterar senha</h2>
        <div class="form-grid">
            <div class="full">
                <label>Senha atual</label>
                <input id="senhaAtual" type="password" autocomplete="current-password">
            </div>
            <div>
                <label>Nova senha</label>
                <input id="senhaNova" type="password" minlength="6" maxlength="72" autocomplete="new-password" placeholder="Mínimo de 6 caracteres">
            </div>
            <div>
                <label>Confirmar nova senha</label>
                <input id="senhaConfirmar" type="password" minlength="6" maxlength="72" autocomplete="new-password">
            </div>
        </div>
        <button class="btn" style="margin-top:15px" onclick="trocarSenha()">Alterar senha</button>
    </div>

    <div class="box">
        <h2>Exportar meus dados</h2>
        <p class="descricao">
            Baixe tarefas, agenda, anotações, gastos e compras em um arquivo CSV
            (abre no Excel ou no Google Planilhas).
        </p>
        <button class="btn" onclick="exportarCSV()">Baixar CSV</button>
    </div>

</section>

<section id="resultados" class="page">

    <div class="titulo">
        <h2>Resultados da busca</h2>
        <p>Itens encontrados no seu MeuDia+.</p>
    </div>

    <div class="box">
        <div id="listaResultados"></div>
    </div>

</section>

</div>

<footer>
    <strong>MeuDia+</strong>
    <br><br>
    Organize tarefas, estudos, trabalho e sua vida financeira
    em um só lugar.
    <br><br>
    © 2026 MeuDia+
</footer>

<script src="painel.js?v=<?= $vJs ?>"></script>

</body>
</html>