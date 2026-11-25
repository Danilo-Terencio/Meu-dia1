/* ================= DADOS DO USUÁRIO (vêm do MySQL) ================= */

const DADOS = window.MEUDIA.dados;

const ler = (chave, padrao = []) =>
    (DADOS[chave] !== undefined && DADOS[chave] !== null) ? DADOS[chave] : padrao;

let tasks = ler("tarefas"),
    agenda = ler("agenda"),
    notas = ler("notas"),
    gastos = ler("gastos"),
    compras = ler("compras"),
    estudos = ler("estudos"),
    trabalhos = ler("trabalhos"),
    renda = Number(ler("renda", 0));


/* ================= FUNÇÕES AUXILIARES ================= */

const $ = id => document.getElementById(id);

const valor = id => $(id).value.trim();

const limpar = (...ids) =>
    ids.forEach(id => $(id).value = "");

const ultimoSalvo = {};
Object.keys(DADOS).forEach(k => { ultimoSalvo[k] = JSON.stringify(DADOS[k]); });

// Salva no banco só o que realmente mudou
const salvar = (chave, dados) => {
    const json = JSON.stringify(dados);
    DADOS[chave] = dados;

    if (ultimoSalvo[chave] === json) return;
    ultimoSalvo[chave] = json;

    fetch("api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ chave: chave, valor: dados })
    })
    .then(r => {
        if (r.status === 401) {
            alert("Your session has expired. Please log in again.");
            window.location.href = "login.php";
            return;
        }
        if (!r.ok) {
            delete ultimoSalvo[chave];
            avisar("Unable to save. Please try again.");
        }
    })
    .catch(() => {
        delete ultimoSalvo[chave];
        avisar("No connection to the server. Your changes may not have been saved.");
    });
};

function dinheiro(valor){
    return Number(valor).toLocationeString("en-GB",{
        style:"currency",
        currency:"BRL"
    });
}


/* ================= MENU ================= */

function abrirPagina(id, botao){

    document.querySelectorAll(".page")
        .forEach(p => p.classList.remove("active"));

    $(id).classList.add("active");

    document.querySelectorAll("nav button")
        .forEach(b => b.classList.remove("active"));

    if(botao) botao.classList.add("active");

    window.scrollTo({
        top:0,
        behavior:"smooth"
    });

    renderizar();
}

function abrirPaginaSemMenu(id){

    document.querySelectorAll(".page")
        .forEach(p => p.classList.remove("active"));

    $(id).classList.add("active");

    window.scrollTo({
        top:0,
        behavior:"smooth"
    });
}


/* ================= TAREFAS ================= */

function adicionarTask(){
    const texto = valor("tarefaText");
    const prioridade = $("tarefaPriority").value;
    const prazo = $("tarefaDeadline").value;

    if(!texto){
        alert("Digite uma task.");
        return;
    }

    tasks.push({
        id:Date.now(),
        texto,
        prioridade,
        prazo,
        concluida:false
    });

    salvarTudo();
    limpar("tarefaText","tarefaDeadline");
    renderizar();
}

function concluirTask(id){

    const task = tasks.find(t => t.id === id);

    if(tarefa) task.concluida = !tarefa.concluida;

    salvarTudo();
    renderizar();
}

function removerTask(id){
    if(!confirm("Tem certeza que deseja excluir este item?")) return;

    tasks = tasks.filter(t => t.id !== id);

    salvarTudo();
    renderizar();
}


/* ================= AGENDA ================= */

function adicionarEvento(){

    const nome = valor("eventoName");
    const data = $("eventoDate").value;
    const hora = $("eventoHora").value;
    const local = valor("eventoLocation");

    if(!nome || !data){
        alert("Enter the event and date.");
        return;
    }

    agenda.push({
        id:Date.now(),
        nome,
        data,
        hora,
        local
    });

    salvarTudo();
    limpar(
        "eventoName",
        "eventoDate",
        "eventoHora",
        "eventoLocation"
    );

    renderizar();
}

function removerEvento(id){
    if(!confirm("Tem certeza que deseja excluir este item?")) return;

    agenda = agenda.filter(e => e.id !== id);

    salvarTudo();
    renderizar();
}


/* ================= NOTAS ================= */

function adicionarNota(){

    const titulo = valor("notaTitulo");
    const texto = valor("notaText");

    if(!titulo || !texto){
        alert("Please enter a title and text.");
        return;
    }

    notas.unshift({
        id:Date.now(),
        titulo,
        texto,
        data:new Date().toLocationeDateString("en-GB")
    });

    salvarTudo();
    limpar("notaTitulo","notaText");
    renderizar();
}

function removerNota(id){
    if(!confirm("Tem certeza que deseja excluir este item?")) return;

    notas = notas.filter(n => n.id !== id);

    salvarTudo();
    renderizar();
}


/* ================= FINANÇAS ================= */

function salvarIncome(){

    const valorIncome = Number($("rendaInput").value);

    if(isNaN(valorIncome) || valorIncome < 0){
        alert("Enter a valid income amount.");
        return;
    }

    renda = valorIncome;

    salvar("renda", renda);

    alert("Income salva com sucesso!");

    renderizar();
}

function adicionarGasto(){

    const descricao = valor("gastoDescricao");
    const valorGasto = Number($("gastoAmount").value);
    const categoria = $("gastoCategory").value;

    if(!descricao || !valorGasto || valorGasto <= 0){
        alert("Enter a description and amount.");
        return;
    }

    gastos.unshift({
        id:Date.now(),
        descricao,
        valor:valorGasto,
        categoria,
        data:new Date().toLocationeDateString("en-GB")
    });

    salvarTudo();
    limpar("gastoDescricao","gastoAmount");
    renderizar();
}

function removerGasto(id){
    if(!confirm("Tem certeza que deseja excluir este item?")) return;

    gastos = gastos.filter(g => g.id !== id);

    salvarTudo();
    renderizar();
}


/* ================= COMPRAS ================= */

function adicionarCompra(){

    const produto = valor("produtoInput");

    if(!produto){
        alert("Enter a product.");
        return;
    }

    compras.push({
        id:Date.now(),
        produto,
        comprado:false
    });

    salvarTudo();
    limpar("produtoInput");
    renderizar();
}

function marcarCompra(id){

    const item = compras.find(c => c.id === id);

    if(item) item.comprado = !item.comprado;

    salvarTudo();
    renderizar();
}

function removerCompra(id){
    if(!confirm("Tem certeza que deseja excluir este item?")) return;

    compras = compras.filter(c => c.id !== id);

    salvarTudo();
    renderizar();
}


/* ================= ESTUDOS ================= */

function adicionarEstudo(){

    const materia = valor("materia");
    const atividade = valor("atividade");
    const data = $("dataEstudo").value;

    if(!materia || !atividade){
        alert("Please enter a subject and an assignment.");
        return;
    }

    estudos.push({
        id:Date.now(),
        materia,
        atividade,
        data,
        concluida:false
    });

    salvarTudo();

    limpar(
        "materia",
        "atividade",
        "dataEstudo"
    );

    renderizar();
}

function concluirEstudo(id){

    const item = estudos.find(e => e.id === id);

    if(item) item.concluida = !item.concluida;

    salvarTudo();
    renderizar();
}

function removerEstudo(id){
    if(!confirm("Tem certeza que deseja excluir este item?")) return;

    estudos = estudos.filter(e => e.id !== id);

    salvarTudo();
    renderizar();
}


/* ================= TRABALHO ================= */

function adicionarWork(){

    const texto = valor("trabalhoText");
    const data = $("trabalhoDate").value;
    const obs = valor("trabalhoObs");

    if(!texto){
        alert("Digite a atividade.");
        return;
    }

    trabalhos.push({
        id:Date.now(),
        texto,
        data,
        obs,
        concluida:false
    });

    salvarTudo();

    limpar(
        "trabalhoText",
        "trabalhoDate",
        "trabalhoObs"
    );

    renderizar();
}

function concluirWork(id){

    const item = trabalhos.find(t => t.id === id);

    if(item) item.concluida = !item.concluida;

    salvarTudo();
    renderizar();
}

function removerWork(id){
    if(!confirm("Tem certeza que deseja excluir este item?")) return;

    trabalhos = trabalhos.filter(t => t.id !== id);

    salvarTudo();
    renderizar();
}


/* ================= PERFIL ================= */

function salvarPerfil(){

    const campos = {
        nome:"perfilName",
        cidade:"perfilCity",
        profissao:"perfilProfissao",
        objetivo:"perfilGoal",
        sobre:"perfilSobre"
    };

    Object.entries(campos).forEach(([chave,id]) => {
        salvar(chave, $(id).value);
    });

    alert("Perfil salvo!");

    atualizarSaudacao();
}

function carregarPerfil(){

    const campos = {
        nome:"perfilName",
        cidade:"perfilCity",
        profissao:"perfilProfissao",
        objetivo:"perfilGoal",
        sobre:"perfilSobre"
    };

    Object.entries(campos).forEach(([chave,id]) => {
        $(id).value = ler(chave, "");
    });
}

function atualizarSaudacao(){

    const nomeLogin = window.MEUDIA.nomeLogin;
    const nome = ler("nome", "") || nomeLogin;

    $("saudacao").textContent =
        nome ? "Olá, " + nome + "!" : "Hello!";
}


/* ================= BUSCA ================= */

function buscarTudo(){

    const termo = valor("buscaGeral").toLowerCase();

    if(!termo){
        alert("Digite algo para pesquisar.");
        return;
    }

    const resultados = [];

    const pesquisar = (lista, tipo, campos, texto) => {

        lista.forEach(item => {

            const encontrado = campos.some(campo =>
                String(item[campo] || "")
                    .toLowerCase()
                    .includes(termo)
            );

            if(encontrado){
                resultados.push({
                    tipo,
                    texto:typeof texto === "function"
                        ? texto(item)
                        : item[texto]
                });
            }
        });
    };

    pesquisar(
        tasks,
        "Task",
        ["texto"],
        "texto"
    );

    pesquisar(
        notas,
        "Anotação",
        ["titulo","texto"],
        "titulo"
    );

    pesquisar(
        compras,
        "Compra",
        ["produto"],
        "produto"
    );

    pesquisar(
        estudos,
        "Estudo",
        ["materia","atividade"],
        e => e.materia + " - " + e.atividade
    );

    pesquisar(
        trabalhos,
        "Work",
        ["texto"],
        "texto"
    );

    const div = $("listaResultados");

    div.innerHTML = resultados.length
        ? resultados.map(r => `
            <div class="resultado">
                <strong>${esc(r.tipo)}</strong><br>
                ${esc(r.texto)}
            </div>
        `).join("")
        : `<div class="resultado">No results found.</div>`;

    abrirPaginaSemMenu("resultados");
}


/* ================= RENDERIZAÇÃO ================= */

function renderizar(){

    renderTasks();
    renderCalendar();
    renderNotes();
    renderFinancas();
    renderShopping();
    renderStudies();
    renderWork();
    atualizarDashboard();
}


/* ================= RENDER TAREFAS ================= */

function renderTasks(){
    const div = $("listaTasks");

    if(!tarefas.length){
        div.innerHTML = `<p class="vazio">No tasks added yet.</p>`;
        return;
    }

    div.innerHTML = tasks.map(t => `
        <div class="item">
            <div class="item-left">
                <input
                    class="check"
                    type="checkbox"
                    ${t.concluida ? "checked" : ""}
                    onchange="concluirTask(${t.id})">
                <div>
                    <strong class="texto-multi ${t.concluida ? "concluida" : ""}">${esc(t.texto)}</strong>
                    <br>
                    <small>Priority: ${esc(t.prioridade)}</small>
                    ${t.prazo ? "<br>" + etiquetaDeadline(t) : ""}
                </div>
            </div>
            <div class="item-actions">
                <button class="btn btn-secondary" onclick="editarTask(${t.id})">
                    Editar
                </button>
                <button class="btn btn-danger" onclick="removerTask(${t.id})">
                    Excluir
                </button>
            </div>
        </div>
    `).join("");
}


/* ================= RENDER AGENDA ================= */

function renderCalendar(){
    const div = $("listaCalendar");

    if(!agenda.length){
        div.innerHTML = `<p class="vazio">No events added yet.</p>`;
        return;
    }

    agenda.sort((a,b) =>
        (a.data + (a.hora || "")).localeCompare(b.data + (b.hora || ""))
    );

    div.innerHTML = agenda.map(e => `
        <div class="item">
            <div>
                <strong>${esc(e.nome)}</strong>
                <br>
                <small>
                    ${formatarDate(e.data)}
                    ${e.hora ? " • ⏰ " + esc(e.hora) : ""}
                    ${e.local ? " • " + esc(e.local) : ""}
                </small>
            </div>
            <div class="item-actions">
                <button class="btn btn-secondary" onclick="editarEvento(${e.id})">
                    Editar
                </button>
                <button class="btn btn-danger" onclick="removerEvento(${e.id})">
                    Excluir
                </button>
            </div>
        </div>
    `).join("");
}


/* ================= RENDER NOTAS ================= */

function renderNotes(){
    const div = $("listaNotes");

    if(!notas.length){
        div.innerHTML = `<p class="vazio">No notes saved yet.</p>`;
        return;
    }

    div.innerHTML = notas.map(n => `
        <div class="item">
            <div>
                <strong>${esc(n.titulo)}</strong>
                <br>
                <small class="texto-multi">${esc(n.texto)}</small>
                <br>
                <small>${esc(n.data)}</small>
            </div>
            <div class="item-actions">
                <button class="btn btn-secondary" onclick="editarNota(${n.id})">
                    Editar
                </button>
                <button class="btn btn-danger" onclick="removerNota(${n.id})">
                    Excluir
                </button>
            </div>
        </div>
    `).join("");
}


/* ================= RENDER FINANÇAS ================= */

function renderFinancas(){
    const total = gastos.reduce(
        (s,g) => s + Number(g.valor),
        0
    );

    const saldo = renda - total;

    $("rendaAmount").textContent = dinheiro(renda);
    $("gastosAmount").textContent = dinheiro(total);
    $("saldoAmount").textContent = dinheiro(saldo);

    $("dashIncome").textContent = dinheiro(renda);
    $("dashExpenses").textContent = dinheiro(total);
    $("dashBalance").textContent = dinheiro(saldo);

    renderGrafico();

    const div = $("listaExpenses");

    if(!gastos.length){
        div.innerHTML = `<p class="vazio">No expenses added yet.</p>`;
        return;
    }

    div.innerHTML = gastos.map(g => `
        <div class="item">
            <div>
                <strong>${esc(g.descricao)}</strong>
                <br>
                <small>
                    ${esc(g.categoria)} • ${esc(g.data)}
                </small>
            </div>
            <div>
                <strong class="saida">
                    ${dinheiro(g.valor)}
                </strong>
                <div class="item-actions" style="margin-top:5px">
                    <button class="btn btn-secondary" onclick="editarGasto(${g.id})">
                        Editar
                    </button>
                    <button class="btn btn-danger" onclick="removerGasto(${g.id})">
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    `).join("");
}


/* ================= RENDER COMPRAS ================= */

function renderShopping(){
    const div = $("listaShopping");

    if(!compras.length){
        div.innerHTML = `<p class="vazio">Your list is empty.</p>`;
        return;
    }

    div.innerHTML = compras.map(c => `
        <div class="compra">
            <label style="display:flex;gap:10px;align-items:center">
                <input
                    type="checkbox"
                    ${c.comprado ? "checked" : ""}
                    onchange="marcarCompra(${c.id})">
                <span class="${c.comprado ? "concluida" : ""}">
                    ${esc(c.produto)}
                </span>
            </label>
            <div class="item-actions">
                <button class="editar" onclick="editarCompra(${c.id})">
                    Editar
                </button>
                <button onclick="removerCompra(${c.id})">
                    Excluir
                </button>
            </div>
        </div>
    `).join("");
}


/* ================= RENDER ESTUDOS ================= */

function renderStudies(){

    const div = $("listaStudies");

    if(!estudos.length){
        div.innerHTML =
            `<p class="vazio">No assignments added yet.</p>`;
        return;
    }

    div.innerHTML = estudos.map(e => `
        <div class="item">

            <div class="item-left">

                <input
                    class="check"
                    type="checkbox"
                    ${e.concluida ? "checked" : ""}
                    onchange="concluirEstudo(${e.id})">

                <div>

                    <strong class="${e.concluida ? "concluida" : ""}">
                        ${esc(e.materia)}
                    </strong>

                    <br>

                    <small>
                        ${esc(e.atividade)}
                        ${e.data ? " • Entrega: " + e.data : ""}
                    </small>

                </div>

            </div>

            <button
                class="btn btn-danger"
                onclick="removerEstudo(${e.id})">
                Excluir
            </button>

        </div>
    `).join("");
}


/* ================= RENDER TRABALHO ================= */

function renderWork(){

    const div = $("listaWork");

    if(!trabalhos.length){
        div.innerHTML =
            `<p class="vazio">
                Nenhuma atividade profissional cadastrada.
            </p>`;
        return;
    }

    div.innerHTML = trabalhos.map(t => `
        <div class="item">

            <div class="item-left">

                <input
                    class="check"
                    type="checkbox"
                    ${t.concluida ? "checked" : ""}
                    onchange="concluirWork(${t.id})">

                <div>

                    <strong class="${t.concluida ? "concluida" : ""}">
                        ${esc(t.texto)}
                    </strong>

                    <br>

                    <small>
                        ${t.data ? "Deadline: " + t.data : ""}
                        ${t.obs ? " • " + esc(t.obs) : ""}
                    </small>

                </div>

            </div>

            <button
                class="btn btn-danger"
                onclick="removerWork(${t.id})">
                Excluir
            </button>

        </div>
    `).join("");
}


/* ================= DASHBOARD ================= */

function atualizarDashboard(){
    $("totalTasks").textContent = tasks.length;
    $("totalCalendar").textContent = agenda.length;
    $("totalShopping").textContent = compras.length;
    $("totalNotes").textContent = notas.length;

    const pendentes = tasksPendentes();
    const overdues = pendentes.filter(t => statusDeadline(t) === "overdue").length;
    const proximas = pendentes.slice(0,5);

    const div = $("resumoTasks");

    if(!proximas.length){
        div.innerHTML = `<p class="vazio">You're all caught up!</p>`;
        return;
    }

    const aviso = overdues
        ? `<div class="aviso-atraso">You have ${overdues} task${overdues > 1 ? "s" : ""} overdue${overdues > 1 ? "s" : ""}.</div>`
        : "";

    div.innerHTML = aviso + proximas.map(t => `
        <div class="resumo-item">
            <strong class="texto-multi">${esc(t.texto)}</strong>
            <br>
            <small>
                ${esc(t.prioridade)}
            </small>
            ${t.prazo ? "<br>" + etiquetaDeadline(t) : ""}
        </div>
    `).join("");
}


/* ================= SALVAR TUDO ================= */

function salvarTudo(){

    const dados = {
        tasks,
        agenda,
        notas,
        gastos,
        compras,
        estudos,
        trabalhos
    };

    Object.entries(dados).forEach(
        ([chave,valor]) => salvar(chave,valor)
    );

    salvar("renda", renda);
}


/* ================= DATA ================= */

function mostrarDate(){

    const agora = new Date();

    const texto = agora.toLocationeDateString(
        "en-GB",
        {
            weekday:"long",
            day:"numeric",
            month:"long",
            year:"numeric"
        }
    );

    $("dataAtual").textContent = texto;
}


/* ================= SEGURANÇA ================= */

function esc(texto){

    return String(texto).replace(
        /[&<>"']/g,
        c => ({
            "&":"&amp;",
            "<":"&lt;",
            ">":"&gt;",
            '"':"&quot;",
            "'":"&#039;"
        })[c]
    );
}


/* ================= DATAS E PRAZOS ================= */
function todayISO(){
    const d = new Date();
    return d.getFullYear() + "-" +
        String(d.getMonth() + 1).padStart(2,"0") + "-" +
        String(d.getDate()).padStart(2,"0");
}

function formatarDate(iso){
    if(!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) return iso || "";
    const [a,m,d] = iso.split("-");
    return d + "/" + m + "/" + a;
}

function statusDeadline(t){
    if(!t.prazo || t.concluida) return "";
    const today = todayISO();
    if(t.prazo < today) return "overdue";
    if(t.prazo === today) return "today";
    return "";
}

function etiquetaDeadline(t){
    if(!t.prazo) return "";
    const s = statusDeadline(t);
    const rotulo = s === "overdue" ? "Overdue • "
                 : s === "today"     ? "⏰ Due today • "
                 :                    "Deadline: ";
    return `<span class="prazo ${s}">${rotulo}${formatarDate(t.prazo)}</span>`;
}

// Pendentes: overdues primeiro, depois as de today, depois por prazo
function tasksPendentes(){
    const peso = t => {
        const s = statusDeadline(t);
        if(s === "overdue") return 0;
        if(s === "today") return 1;
        if(t.prazo) return 2;
        return 3;
    };

    return tasks
        .filter(t => !t.concluida)
        .sort((a,b) => {
            const d = peso(a) - peso(b);
            if(d) return d;
            if(a.prazo && b.prazo) return a.prazo.localeCompare(b.prazo);
            return 0;
        });
}

/* ================= AVISO DE ERRO ================= */
function avisar(texto){
    let el = $("aviso");

    if(!el){
        el = document.createElement("div");
        el.id = "aviso";
        el.className = "aviso";
        document.body.appendChild(el);
    }

    el.textContent = texto;
    el.classList.add("visivel");

    clearTimeout(avisar.tempo);
    avisar.tempo = setTimeout(() => el.classList.remove("visivel"), 5000);
}

/* ================= JANELA DE EDIÇÃO ================= */
// campos: [{chave, label, tipo, opcoes, valor}]
// aoSave(valores) pode devolver false para manter a janela aberta
function abrirEditor(titulo, campos, aoSave){
    const fundo = document.createElement("div");
    fundo.className = "modal-fundo";

    const caixa = document.createElement("div");
    caixa.className = "modal";

    const h = document.createElement("h2");
    h.textContent = titulo;
    caixa.appendChild(h);

    const inputs = {};

    campos.forEach(c => {
        const wrap = document.createElement("div");
        wrap.className = "modal-campo";

        const lb = document.createElement("label");
        lb.textContent = c.label;
        wrap.appendChild(lb);

        let el;

        if(c.tipo === "textarea"){
            el = document.createElement("textarea");
        } else if(c.tipo === "select"){
            el = document.createElement("select");
            c.opcoes.forEach(o => {
                const op = document.createElement("option");
                op.value = o;
                op.textContent = o;
                el.appendChild(op);
            });
        } else {
            el = document.createElement("input");
            el.type = c.tipo || "text";
            if(c.tipo === "number") el.step = "0.01";
        }

        el.value = (c.valor === undefined || c.valor === null) ? "" : c.valor;
        inputs[c.chave] = el;

        wrap.appendChild(el);
        caixa.appendChild(wrap);
    });

    const acoes = document.createElement("div");
    acoes.className = "modal-acoes";

    const cancelar = document.createElement("button");
    cancelar.className = "btn btn-secondary";
    cancelar.textContent = "Cancel";

    const salvarBtn = document.createElement("button");
    salvarBtn.className = "btn";
    salvarBtn.textContent = "Save";

    acoes.appendChild(cancelar);
    acoes.appendChild(salvarBtn);
    caixa.appendChild(acoes);

    const aoTecla = e => { if(e.key === "Escape") fechar(); };

    function fechar(){
        fundo.remove();
        document.removeEventListener("keydown", aoTecla);
    }

    salvarBtn.onclick = () => {
        const valores = {};
        Object.keys(inputs).forEach(k => valores[k] = inputs[k].value.trim());
        if(aoSave(valores) !== false) fechar();
    };

    cancelar.onclick = fechar;

    fundo.addEventListener("click", e => {
        if(e.target === fundo) fechar();
    });

    document.addEventListener("keydown", aoTecla);

    fundo.appendChild(caixa);
    document.body.appendChild(fundo);

    Object.values(inputs)[0].focus();
}

function editarTask(id){
    const t = tasks.find(x => x.id === id);
    if(!t) return;

    abrirEditor("Edit task", [
        {chave:"texto", label:"Task", tipo:"textarea", valor:t.texto},
        {chave:"prioridade", label:"Priority", tipo:"select",
         opcoes:["Normal","Important","Urgent"], valor:t.prioridade},
        {chave:"prazo", label:"Due date (optional)", tipo:"date", valor:t.prazo || ""}
    ], v => {
        if(!v.texto){ alert("Enter a task."); return false; }
        t.texto = v.texto;
        t.prioridade = v.prioridade;
        t.prazo = v.prazo;
        salvarTudo();
        renderizar();
    });
}

function editarEvento(id){
    const e = agenda.find(x => x.id === id);
    if(!e) return;

    abrirEditor("Edit event", [
        {chave:"nome", label:"Event", valor:e.nome},
        {chave:"data", label:"Date", tipo:"date", valor:e.data},
        {chave:"hora", label:"Time", tipo:"time", valor:e.hora || ""},
        {chave:"local", label:"Location", valor:e.local || ""}
    ], v => {
        if(!v.nome || !v.data){ alert("Enter the event and date."); return false; }
        e.nome = v.nome;
        e.data = v.data;
        e.hora = v.hora;
        e.local = v.local;
        salvarTudo();
        renderizar();
    });
}

function editarNota(id){
    const n = notas.find(x => x.id === id);
    if(!n) return;

    abrirEditor("Edit note", [
        {chave:"titulo", label:"Title", valor:n.titulo},
        {chave:"texto", label:"Text", tipo:"textarea", valor:n.texto}
    ], v => {
        if(!v.titulo || !v.texto){ alert("Please enter a title and text."); return false; }
        n.titulo = v.titulo;
        n.texto = v.texto;
        salvarTudo();
        renderizar();
    });
}

function editarGasto(id){
    const g = gastos.find(x => x.id === id);
    if(!g) return;

    abrirEditor("Edit expense", [
        {chave:"descricao", label:"Description", valor:g.descricao},
        {chave:"valor", label:"Amount", tipo:"number", valor:g.valor},
        {chave:"categoria", label:"Category", tipo:"select",
         opcoes:["Food","Transportation","Home","Studies","Leisure","Shopping","Other"],
         valor:g.categoria}
    ], v => {
        const novoAmount = Number(v.valor);
        if(!v.descricao || !novoAmount || novoAmount <= 0){
            alert("Enter a description and amount.");
            return false;
        }
        g.descricao = v.descricao;
        g.valor = novoAmount;
        g.categoria = v.categoria;
        salvarTudo();
        renderizar();
    });
}

function editarCompra(id){
    const c = compras.find(x => x.id === id);
    if(!c) return;

    abrirEditor("Edit item", [
        {chave:"produto", label:"Product", valor:c.produto}
    ], v => {
        if(!v.produto){ alert("Enter a product."); return false; }
        c.produto = v.produto;
        salvarTudo();
        renderizar();
    });
}

/* ================= GRÁFICO DE GASTOS ================= */
function renderGrafico(){
    const div = $("graficoExpenses");
    if(!div) return;

    if(!gastos.length){
        div.innerHTML = `<p class="vazio">Add expenses to view the chart.</p>`;
        return;
    }

    const porCategory = {};
    gastos.forEach(g => {
        porCategory[g.categoria] =
            (porCategory[g.categoria] || 0) + Number(g.valor);
    });

    const total = Object.values(porCategory).reduce((a,b) => a + b, 0);

    div.innerHTML = Object.entries(porCategory)
        .sort((a,b) => b[1] - a[1])
        .map(([categoria, soma]) => {
            const pct = total ? (soma / total * 100) : 0;
            return `
                <div class="barra-linha">
                    <div class="barra-topo">
                        <span>${esc(categoria)}</span>
                        <span>${dinheiro(soma)} • ${pct.toFixed(0)}%</span>
                    </div>
                    <div class="barra">
                        <div class="barra-preenchida" style="width:${pct.toFixed(1)}%"></div>
                    </div>
                </div>
            `;
        }).join("");
}

/* ================= TEMA CLARO / ESCURO ================= */
function aplicarTema(){
    const salvo = ler("tema", null);
    const escuro = salvo
        ? salvo === "escuro"
        : (window.matchMedia && matchMedia("(prefers-color-scheme: dark)").matches);

    document.body.classList.toggle("dark", escuro);

    // Atualiza os interruptores ON/OFF (menu e página Account)
    document.querySelectorAll(".switch").forEach(b => {
        b.setAttribute("aria-checked", escuro ? "true" : "false");
    });
}

function alternarTema(){
    const escuro = !document.body.classList.contains("dark");
    salvar("tema", escuro ? "escuro" : "claro");
    aplicarTema();
}

/* ================= EXPORTAR CSV ================= */
function celulaCSV(v){
    let texto = (v === undefined || v === null) ? "" : String(v);

    // Evita que o Excel execute textos que começam como fórmula
    if(/^[=+\-@\t\r]/.test(texto) && isNaN(Number(texto))){
        texto = "'" + texto;
    }

    return '"' + texto.replace(/"/g, '""') + '"';
}

function exportarCSV(){
    const grupos = [
        ["Tasks", tasks],
        ["Calendar", agenda],
        ["Notes", notas],
        ["Expenses", gastos],
        ["Shopping", compras],
        ["Studies", estudos],
        ["Works", trabalhos]
    ];

    const linhas = [];

    linhas.push(celulaCSV("Income") + ";" + celulaCSV(renda));
    linhas.push("");

    grupos.forEach(([nome, lista]) => {
        if(!Array.isArray(lista) || !lista.length) return;

        const colunas = [];
        lista.forEach(item => {
            if(item && typeof item === "object"){
                Object.keys(item).forEach(k => {
                    if(k !== "id" && !colunas.includes(k)) colunas.push(k);
                });
            }
        });

        linhas.push(celulaCSV(nome));
        linhas.push(colunas.map(celulaCSV).join(";"));

        lista.forEach(item => {
            linhas.push(colunas.map(c => celulaCSV(item ? item[c] : "")).join(";"));
        });

        linhas.push("");
    });

    // \ufeff faz o Excel reconhecer os acentos
    const blob = new Blob(["\ufeff" + linhas.join("\r\n")],
        {type:"text/csv;charset=utf-8"});

    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = "meudia-dados.csv";
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
}

/* ================= ALTERAR SENHA ================= */
async function trocarPassword(){
    const atual = $("senhaAtual").value;
    const nova = $("senhaNova").value;
    const confirmar = $("senhaConfirmar").value;

    if(!atual || !nova || !confirmar){
        alert("Please fill in all fields.");
        return;
    }

    if(nova.length < 6){
        alert("Your new password must be at least 6 characters long.");
        return;
    }

    if(nova !== confirmar){
        alert("The confirmation does not match the new password.");
        return;
    }

    try{
        const r = await fetch("senha.php", {
            method:"POST",
            headers:{ "Content-Type":"application/json" },
            body:JSON.stringify({ atual, nova })
        });

        if(r.status === 401){
            window.location.href = "login.php";
            return;
        }

        const resposta = await r.json();

        if(resposta.ok){
            alert("Password alterada com sucesso!");
            limpar("senhaAtual","senhaNova","senhaConfirmar");
        } else {
            alert(resposta.mensagem || "Unable to change the password.");
        }
    } catch(e){
        alert("Connection error. Please try again.");
    }
}

/* ================= INICIALIZAÇÃO ================= */

aplicarTema();
carregarPerfil();
mostrarDate();
atualizarSaudacao();
renderizar();
