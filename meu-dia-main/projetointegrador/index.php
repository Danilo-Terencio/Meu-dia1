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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MeuDia+ | Organize your life</title>
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
            <p>Organize your life in one place</p>
        </div>
    </div>

    <nav>
        <button class="active" onclick="abrirPagina('inicio',this)"><svg class="ico" aria-hidden="true"><use href="#i-home"/></svg>Home</button>
        <button onclick="abrirPagina('tarefas',this)"><svg class="ico" aria-hidden="true"><use href="#i-tarefas"/></svg>Tasks</button>
        <button onclick="abrirPagina('agenda',this)"><svg class="ico" aria-hidden="true"><use href="#i-agenda"/></svg>Calendar</button>
        <button onclick="abrirPagina('notas',this)"><svg class="ico" aria-hidden="true"><use href="#i-notas"/></svg>Notes</button>
        <button onclick="abrirPagina('financas',this)"><svg class="ico" aria-hidden="true"><use href="#i-financas"/></svg>Finances</button>
        <button onclick="abrirPagina('compras',this)"><svg class="ico" aria-hidden="true"><use href="#i-compras"/></svg>Shopping</button>
        <button onclick="abrirPagina('estudos',this)"><svg class="ico" aria-hidden="true"><use href="#i-estudos"/></svg>Studies</button>
        <button onclick="abrirPagina('conta',this)"><svg class="ico" aria-hidden="true"><use href="#i-conta"/></svg>Account</button>
        <button onclick="window.location.href='logout.php'"><svg class="ico" aria-hidden="true"><use href="#i-sair"/></svg>Log out</button>
        <div class="nav-tema">
            <label for="switchMenu">Dark mode</label>
            <button type="button" id="switchMenu" class="switch" role="switch" aria-checked="false" onclick="alternarTema()"><span class="switch-texto"></span><span class="switch-bola"></span></button>
        </div>
    </nav>
</header>

<div class="container">

<!-- INÍCIO -->
<section id="inicio" class="page active">

    <div class="hero">
        <h2 id="saudacao">Hello!</h2>

        <p>
            Keep your main activities, events,
            notes, and finances organized in one place.
        </p>

        <div class="data-atual" id="dataAtual"></div>

        <div class="search">
            <input id="buscaGeral"
                   placeholder="Search tasks, notes, and shopping...">
            <button onclick="buscarTudo()">Search</button>
        </div>
    </div>

    <div class="grid">

        <div class="card">
            <div class="card-icon"><svg class="ico" aria-hidden="true"><use href="#i-tarefas"/></svg></div>
            <h3>Tasks</h3>
            <p id="totalTarefas" class="stat">0</p>
            <p>tasks created</p>
        </div>

        <div class="card">
            <div class="card-icon"><svg class="ico" aria-hidden="true"><use href="#i-agenda"/></svg></div>
            <h3>Calendar</h3>
            <p id="totalAgenda" class="stat">0</p>
            <p>events</p>
        </div>

        <div class="card">
            <div class="card-icon"><svg class="ico" aria-hidden="true"><use href="#i-compras"/></svg></div>
            <h3>Shopping</h3>
            <p id="totalCompras" class="stat">0</p>
            <p>items on your list</p>
        </div>

        <div class="card">
            <div class="card-icon"><svg class="ico" aria-hidden="true"><use href="#i-notas"/></svg></div>
            <h3>Anotações</h3>
            <p id="totalNotas" class="stat">0</p>
            <p>saved notes</p>
        </div>

    </div>

    <div class="dashboard-grid">

        <div class="box">
            <h2>Upcoming tasks</h2>
            <div id="resumoTarefas"></div>
        </div>

        <div class="box">
            <h2>Financial summary</h2>

            <p>Income:</p>
            <strong id="dashRenda">R$ 0,00</strong>

            <br><br>

            <p>Expenses:</p>
            <strong id="dashGastos">R$ 0,00</strong>

            <br><br>

            <p>Balance</p>
            <strong id="dashSaldo">R$ 0,00</strong>
        </div>

    </div>

</section>

<!-- TAREFAS -->
<section id="tarefas" class="page">

    <div class="titulo">
        <h2>My tasks</h2>
        <p>Organize everything you need to get done.</p>
    </div>

    <div class="box">
        <div class="form-grid">

            <div>
                <label>Task</label>
                <textarea id="tarefaTexto" class="tarefa-texto"
                          placeholder="E.g.: Complete a school assignment (press Enter for a new line)"></textarea>
            </div>

            <div>
                <label>Priority</label>
                <select id="tarefaPrioridade">
                    <option>Normal</option>
                    <option>Important</option>
                    <option>Urgent</option>
                </select>
            </div>
            <div>
                <label>Due date (optional)</label>
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
        <h2>List</h2>
        <div id="listaTarefas"></div>
    </div>

</section>

<!-- AGENDA -->
<section id="agenda" class="page">

    <div class="titulo">
        <h2>My calendar</h2>
        <p>Add your events.</p>
    </div>

    <div class="box">
        <div class="form-grid">

            <div>
                <label>Event</label>
                <input id="eventoNome"
                       placeholder="E.g.: Doctor's appointment">
            </div>

            <div>
                <label>Date</label>
                <input id="eventoData" type="date">
            </div>

            <div>
                <label>Time</label>
                <input id="eventoHora" type="time">
            </div>

            <div>
                <label>Location</label>
                <input id="eventoLocal"
                       placeholder="E.g.: Downtown">
            </div>

        </div>

        <button class="btn"
                style="margin-top:15px"
                onclick="adicionarEvento()">
            Adicionar compromisso
        </button>
    </div>

    <div class="box">
        <h2>My events</h2>
        <div id="listaAgenda"></div>
    </div>

</section>

<!-- NOTAS -->
<section id="notas" class="page">

    <div class="titulo">
        <h2>My notes</h2>
        <p>Save ideas, information, and reminders.</p>
    </div>

    <div class="box">

        <label>Title</label>
        <input id="notaTitulo"
               placeholder="Note title">

        <br><br>

        <label>Text</label>
        <textarea id="notaTexto"
                  placeholder="Write here..."></textarea>

        <button class="btn"
                style="margin-top:15px"
                onclick="adicionarNota()">
            Salvar anotação
        </button>

    </div>

    <div class="box">
        <h2>Saved notes</h2>
        <div id="listaNotas"></div>
    </div>

</section>

<!-- FINANÇAS -->
<section id="financas" class="page">

    <div class="titulo">
        <h2>My finances</h2>
        <p>Track your income and expenses.</p>
    </div>

    <div class="finance-grid">

        <div class="finance-card">
            <h3>Income</h3>
            <div id="rendaValor" class="finance-value entrada">
                R$ 0,00
            </div>
        </div>

        <div class="finance-card">
            <h3>Expenses</h3>
            <div id="gastosValor" class="finance-value saida">
                R$ 0,00
            </div>
        </div>

        <div class="finance-card">
            <h3>Balance</h3>
            <div id="saldoValor" class="finance-value saldo">
                R$ 0,00
            </div>
        </div>

    </div>

    <div class="box" style="margin-top:20px">

        <h2>My income</h2>

        <div class="form-grid">

            <div>
                <label>Income amount</label>
                <input id="rendaInput"
                       type="number"
                       step="0.01"
                       placeholder="E.g.: 2500">
            </div>

            <div>
                <label>Source</label>
                <input id="rendaFonte"
                       placeholder="E.g.: Salary">
            </div>

        </div>

        <button class="btn"
                style="margin-top:15px"
                onclick="salvarRenda()">
            Salvar renda
        </button>

    </div>

    <div class="box">

        <h2>Add expense</h2>

        <div class="form-grid">

            <div>
                <label>Description</label>
                <input id="gastoDescricao"
                       placeholder="E.g.: Groceries">
            </div>

            <div>
                <label>Amount</label>
                <input id="gastoValor"
                       type="number"
                       step="0.01"
                       placeholder="E.g.: 100">
            </div>

            <div>
                <label>Category</label>
                <select id="gastoCategoria">
                    <option>Food</option>
                    <option>Transportation</option>
                    <option>Home</option>
                    <option>Studies</option>
                    <option>Leisure</option>
                    <option>Shopping</option>
                    <option>Other</option>
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
        <h2>Expenses by category</h2>
        <div id="graficoGastos"></div>
    </div>

    <div class="box">
        <h2>Expense history</h2>
        <div id="listaGastos"></div>
    </div>

</section>

<!-- COMPRAS -->
<section id="compras" class="page">

    <div class="titulo">
        <h2>Shopping list</h2>
        <p>Don't forget what you need to buy.</p>
    </div>

    <div class="box">
        <div class="search">
            <input id="produtoInput"
                   placeholder="E.g.: Rice, milk, bread...">
            <button onclick="adicionarCompra()">Add</button>
        </div>
    </div>

    <div class="box">
        <h2>My list</h2>
        <div id="listaCompras"></div>
    </div>

</section>

<!-- ESTUDOS -->
<section id="estudos" class="page">

    <div class="titulo">
        <h2>Studies</h2>
        <p>Organize your subjects and school assignments.</p>
    </div>

    <div class="box">

        <div class="form-grid">

            <div>
                <label>Subject</label>
                <input id="materia"
                       placeholder="E.g.: Mathematics">
            </div>

            <div>
                <label>Assignment</label>
                <input id="atividade"
                       placeholder="E.g.: Complete exercises">
            </div>

            <div>
                <label>Due date</label>
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
        <h2>Assignments</h2>
        <div id="listaEstudos"></div>
    </div>

</section>

<!-- TRABALHO -->
<section id="trabalho" class="page">

    <div class="titulo">
        <h2>Work</h2>
        <p>Organize your work activities.</p>
    </div>

    <div class="box">

        <div class="form-grid">

            <div>
                <label>Work task</label>
                <input id="trabalhoTexto"
                       placeholder="E.g.: Submit a report">
            </div>

            <div>
                <label>Deadline</label>
                <input id="trabalhoData" type="date">
            </div>

            <div class="full">
                <label>Notes</label>
                <textarea id="trabalhoObs"
                          placeholder="Additional information..."></textarea>
            </div>

        </div>

        <button class="btn"
                style="margin-top:15px"
                onclick="adicionarTrabalho()">
            Adicionar atividade
        </button>

    </div>

    <div class="box">
        <h2>Work activities</h2>
        <div id="listaTrabalho"></div>
    </div>

</section>

<!-- EMERGÊNCIA -->
<section id="emergencia" class="page">

    <div class="alerta">
        <h2>Emergency</h2>
        <p>
            In an emergency, move to a safe place
            and contact the appropriate emergency service.
        </p>
    </div>

    <div class="emergencias">

        <div class="emergencia">
            <div class="emergencia-icon"><svg class="ico" aria-hidden="true"><use href="#i-policia"/></svg></div>
            <h3>Police</h3>
            <p>Police emergencies.</p>
            <a class="ligar" href="tel:190">Call 190</a>
        </div>

        <div class="emergencia">
            <div class="emergencia-icon"><svg class="ico" aria-hidden="true"><use href="#i-samu"/></svg></div>
            <h3>Emergency Medical Service</h3>
            <p>Emergency medical assistance.</p>
            <a class="ligar" href="tel:192">Call 192</a>
        </div>

        <div class="emergencia">
            <div class="emergencia-icon"><svg class="ico" aria-hidden="true"><use href="#i-bombeiros"/></svg></div>
            <h3>Fire Department</h3>
            <p>Fires and other emergencies.</p>
            <a class="ligar" href="tel:193">Call 193</a>
        </div>

        <div class="emergencia">
            <div class="emergencia-icon"><svg class="ico" aria-hidden="true"><use href="#i-rodovia"/></svg></div>
            <h3>Federal Highway Police</h3>
            <p>Emergencies on federal highways.</p>
            <a class="ligar" href="tel:191">Call 191</a>
        </div>

        <div class="emergencia">
            <div class="emergencia-icon"><svg class="ico" aria-hidden="true"><use href="#i-defesa"/></svg></div>
            <h3>Civil Defense</h3>
            <p>Disasters and hazardous situations.</p>
            <a class="ligar" href="tel:199">Call 199</a>
        </div>

        <div class="emergencia">
            <div class="emergencia-icon"><svg class="ico" aria-hidden="true"><use href="#i-denuncia"/></svg></div>
            <h3>Crime Hotline</h3>
            <p>Contact the service available in your area.</p>
        </div>

    </div>

</section>

<!-- PERFIL -->
<section id="perfil" class="page">

    <div class="titulo">
        <h2>My profile</h2>
        <p>Manage your information.</p>
    </div>

    <div class="box">

        <div class="form-grid">

            <div>
                <label>Name</label>
                <input id="perfilNome"
                       placeholder="Your name">
            </div>

            <div>
                <label>City</label>
                <input id="perfilCidade"
                       placeholder="Your city">
            </div>

            <div>
                <label>Occupation</label>
                <input id="perfilProfissao"
                       placeholder="Your occupation">
            </div>

            <div>
                <label>Goal</label>
                <input id="perfilObjetivo"
                       placeholder="E.g.: Organize my routine">
            </div>

            <div class="full">
                <label>About you</label>
                <textarea id="perfilSobre"
                          placeholder="Write a few details about yourself..."></textarea>
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
        <h2>My account</h2>
        <p>Appearance, security, and your data.</p>
    </div>

    <div class="box">
        <h2>Appearance</h2>
        <p class="descricao">Turn on dark mode to give your dashboard a dark background.</p>
        <div class="linha-switch">
            <button type="button" class="switch grande" role="switch" aria-checked="false" aria-label="Dark mode" onclick="alternarTema()"><span class="switch-texto"></span><span class="switch-bola"></span></button>
            <strong>Dark mode</strong>
        </div>
    </div>

    <div class="box">
        <h2>Change password</h2>
        <div class="form-grid">
            <div class="full">
                <label>Current password</label>
                <input id="senhaAtual" type="password" autocomplete="current-password">
            </div>
            <div>
                <label>New password</label>
                <input id="senhaNova" type="password" minlength="6" maxlength="72" autocomplete="new-password" placeholder="At least 6 characters">
            </div>
            <div>
                <label>Confirm new password</label>
                <input id="senhaConfirmar" type="password" minlength="6" maxlength="72" autocomplete="new-password">
            </div>
        </div>
        <button class="btn" style="margin-top:15px" onclick="trocarSenha()">Change password</button>
    </div>

    <div class="box">
        <h2>Export my data</h2>
        <p class="descricao">
            Download your tasks, calendar, notes, expenses, and shopping list as a CSV file
            (opens in Excel or Google Sheets).
        </p>
        <button class="btn" onclick="exportarCSV()">Download CSV</button>
    </div>

</section>

<section id="resultados" class="page">

    <div class="titulo">
        <h2>Search results</h2>
        <p>Items found in your MeuDia+.</p>
    </div>

    <div class="box">
        <div id="listaResultados"></div>
    </div>

</section>

</div>

<footer>
    <strong>MeuDia+</strong>
    <br><br>
    Organize tasks, studies, work, and your finances
    em um só lugar.
    <br><br>
    © 2026 MeuDia+
</footer>

<script src="painel.js?v=<?= $vJs ?>"></script>

</body>
</html>