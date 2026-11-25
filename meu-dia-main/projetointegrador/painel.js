/* ================= DADOS DO USUÁRIO (vêm do MySQL) ================= */

const DADOS = window.MEUDIA.dados;

const ler = (chave, padrao = []) =>
    (DADOS[chave] !== undefined && DADOS[chave] !== null) ? DADOS[chave] : padrao;

let tarefas = ler("tarefas"),
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
            alert("Sua sessão expirou. Faça login novamente.");
            window.location.href = "login.php";
            return;
        }
        if (!r.ok) {
            delete ultimoSalvo[chave];
            avisar("Não foi possível salvar. Tente novamente.");
        }
    })
    .catch(() => {
        delete ultimoSalvo[chave];
        avisar("Sem conexão com o servidor. Suas alterações podem não ter sido salvas.");
    });
};

function dinheiro(valor){
    return Number(valor).toLocaleString("pt-BR",{
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

function adicionarTarefa(){
    const texto = valor("tarefaTexto");
    const prioridade = $("tarefaPrioridade").value;
    const prazo = $("tarefaPrazo").value;

    if(!texto){
        alert("Digite uma tarefa.");
        return;
    }

    tarefas.push({
        id:Date.now(),
        texto,
        prioridade,
        prazo,
        concluida:false
    });

    salvarTudo();
    limpar("tarefaTexto","tarefaPrazo");
    renderizar();
}

function concluirTarefa(id){

    const tarefa = tarefas.find(t => t.id === id);

    if(tarefa) tarefa.concluida = !tarefa.concluida;

    salvarTudo();
    renderizar();
}

function removerTarefa(id){
    if(!confirm("Tem certeza que deseja excluir este item?")) return;

    tarefas = tarefas.filter(t => t.id !== id);

    salvarTudo();
    renderizar();
}


/* ================= AGENDA ================= */

function adicionarEvento(){

    const nome = valor("eventoNome");
    const data = $("eventoData").value;
    const hora = $("eventoHora").value;
    const local = valor("eventoLocal");

    if(!nome || !data){
        alert("Informe o compromisso e a data.");
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
        "eventoNome",
        "eventoData",
        "eventoHora",
        "eventoLocal"
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
    const texto = valor("notaTexto");

    if(!titulo || !texto){
        alert("Preencha título e texto.");
        return;
    }

    notas.unshift({
        id:Date.now(),
        titulo,
        texto,
        data:new Date().toLocaleDateString("pt-BR")
    });

    salvarTudo();
    limpar("notaTitulo","notaTexto");
    renderizar();
}

function removerNota(id){
    if(!confirm("Tem certeza que deseja excluir este item?")) return;

    notas = notas.filter(n => n.id !== id);

    salvarTudo();
    renderizar();
}


/* ================= FINANÇAS ================= */

function salvarRenda(){

    const valorRenda = Number($("rendaInput").value);

    if(isNaN(valorRenda) || valorRenda < 0){
        alert("Informe uma renda válida.");
        return;
    }

    renda = valorRenda;

    salvar("renda", renda);

    alert("Renda salva com sucesso!");

    renderizar();
}

function adicionarGasto(){

    const descricao = valor("gastoDescricao");
    const valorGasto = Number($("gastoValor").value);
    const categoria = $("gastoCategoria").value;

    if(!descricao || !valorGasto || valorGasto <= 0){
        alert("Informe descrição e valor.");
        return;
    }

    gastos.unshift({
        id:Date.now(),
        descricao,
        valor:valorGasto,
        categoria,
        data:new Date().toLocaleDateString("pt-BR")
    });

    salvarTudo();
    limpar("gastoDescricao","gastoValor");
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
        alert("Digite um produto.");
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
        alert("Preencha matéria e atividade.");
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

function adicionarTrabalho(){

    const texto = valor("trabalhoTexto");
    const data = $("trabalhoData").value;
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
        "trabalhoTexto",
        "trabalhoData",
        "trabalhoObs"
    );

    renderizar();
}

function concluirTrabalho(id){

    const item = trabalhos.find(t => t.id === id);

    if(item) item.concluida = !item.concluida;

    salvarTudo();
    renderizar();
}

function removerTrabalho(id){
    if(!confirm("Tem certeza que deseja excluir este item?")) return;

    trabalhos = trabalhos.filter(t => t.id !== id);

    salvarTudo();
    renderizar();
}


/* ================= PERFIL ================= */

function salvarPerfil(){

    const campos = {
        nome:"perfilNome",
        cidade:"perfilCidade",
        profissao:"perfilProfissao",
        objetivo:"perfilObjetivo",
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
        nome:"perfilNome",
        cidade:"perfilCidade",
        profissao:"perfilProfissao",
        objetivo:"perfilObjetivo",
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
        nome ? "Olá, " + nome + "!" : "Olá!";
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
        tarefas,
        "Tarefa",
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
        "Trabalho",
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
        : `<div class="resultado">Nenhum resultado encontrado.</div>`;

    abrirPaginaSemMenu("resultados");
}


/* ================= RENDERIZAÇÃO ================= */

function renderizar(){

    renderTarefas();
    renderAgenda();
    renderNotas();
    renderFinancas();
    renderCompras();
    renderEstudos();
    renderTrabalho();
    atualizarDashboard();
}


/* ================= RENDER TAREFAS ================= */

function renderTarefas(){
    const div = $("listaTarefas");

    if(!tarefas.length){
        div.innerHTML = `<p class="vazio">Nenhuma tarefa cadastrada.</p>`;
        return;
    }

    div.innerHTML = tarefas.map(t => `
        <div class="item">
            <div class="item-left">
                <input
                    class="check"
                    type="checkbox"
                    ${t.concluida ? "checked" : ""}
                    onchange="concluirTarefa(${t.id})">
                <div>
                    <strong class="texto-multi ${t.concluida ? "concluida" : ""}">${esc(t.texto)}</strong>
                    <br>
                    <small>Prioridade: ${esc(t.prioridade)}</small>
                    ${t.prazo ? "<br>" + etiquetaPrazo(t) : ""}
                </div>
            </div>
            <div class="item-actions">
                <button class="btn btn-secondary" onclick="editarTarefa(${t.id})">
                    Editar
                </button>
                <button class="btn btn-danger" onclick="removerTarefa(${t.id})">
                    Excluir
                </button>
            </div>
        </div>
    `).join("");
}


/* ================= RENDER AGENDA ================= */

function renderAgenda(){
    const div = $("listaAgenda");

    if(!agenda.length){
        div.innerHTML = `<p class="vazio">Nenhum compromisso cadastrado.</p>`;
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
                    ${formatarData(e.data)}
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

function renderNotas(){
    const div = $("listaNotas");

    if(!notas.length){
        div.innerHTML = `<p class="vazio">Nenhuma anotação salva.</p>`;
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

    $("rendaValor").textContent = dinheiro(renda);
    $("gastosValor").textContent = dinheiro(total);
    $("saldoValor").textContent = dinheiro(saldo);

    $("dashRenda").textContent = dinheiro(renda);
    $("dashGastos").textContent = dinheiro(total);
    $("dashSaldo").textContent = dinheiro(saldo);

    renderGrafico();

    const div = $("listaGastos");

    if(!gastos.length){
        div.innerHTML = `<p class="vazio">Nenhum gasto cadastrado.</p>`;
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

function renderCompras(){
    const div = $("listaCompras");

    if(!compras.length){
        div.innerHTML = `<p class="vazio">Sua lista está vazia.</p>`;
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

function renderEstudos(){

    const div = $("listaEstudos");

    if(!estudos.length){
        div.innerHTML =
            `<p class="vazio">Nenhuma atividade cadastrada.</p>`;
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

function renderTrabalho(){

    const div = $("listaTrabalho");

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
                    onchange="concluirTrabalho(${t.id})">

                <div>

                    <strong class="${t.concluida ? "concluida" : ""}">
                        ${esc(t.texto)}
                    </strong>

                    <br>

                    <small>
                        ${t.data ? "Prazo: " + t.data : ""}
                        ${t.obs ? " • " + esc(t.obs) : ""}
                    </small>

                </div>

            </div>

            <button
                class="btn btn-danger"
                onclick="removerTrabalho(${t.id})">
                Excluir
            </button>

        </div>
    `).join("");
}


/* ================= DASHBOARD ================= */

function atualizarDashboard(){
    $("totalTarefas").textContent = tarefas.length;
    $("totalAgenda").textContent = agenda.length;
    $("totalCompras").textContent = compras.length;
    $("totalNotas").textContent = notas.length;

    const pendentes = tarefasPendentes();
    const atrasadas = pendentes.filter(t => statusPrazo(t) === "atrasada").length;
    const proximas = pendentes.slice(0,5);

    const div = $("resumoTarefas");

    if(!proximas.length){
        div.innerHTML = `<p class="vazio">Tudo em dia!</p>`;
        return;
    }

    const aviso = atrasadas
        ? `<div class="aviso-atraso">Você tem ${atrasadas} tarefa${atrasadas > 1 ? "s" : ""} atrasada${atrasadas > 1 ? "s" : ""}.</div>`
        : "";

    div.innerHTML = aviso + proximas.map(t => `
        <div class="resumo-item">
            <strong class="texto-multi">${esc(t.texto)}</strong>
            <br>
            <small>
                ${esc(t.prioridade)}
            </small>
            ${t.prazo ? "<br>" + etiquetaPrazo(t) : ""}
        </div>
    `).join("");
}


/* ================= SALVAR TUDO ================= */

function salvarTudo(){

    const dados = {
        tarefas,
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

function mostrarData(){

    const agora = new Date();

    const texto = agora.toLocaleDateString(
        "pt-BR",
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
function hojeISO(){
    const d = new Date();
    return d.getFullYear() + "-" +
        String(d.getMonth() + 1).padStart(2,"0") + "-" +
        String(d.getDate()).padStart(2,"0");
}

function formatarData(iso){
    if(!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) return iso || "";
    const [a,m,d] = iso.split("-");
    return d + "/" + m + "/" + a;
}

function statusPrazo(t){
    if(!t.prazo || t.concluida) return "";
    const hoje = hojeISO();
    if(t.prazo < hoje) return "atrasada";
    if(t.prazo === hoje) return "hoje";
    return "";
}

function etiquetaPrazo(t){
    if(!t.prazo) return "";
    const s = statusPrazo(t);
    const rotulo = s === "atrasada" ? "Atrasada • "
                 : s === "hoje"     ? "⏰ Vence hoje • "
                 :                    "Prazo: ";
    return `<span class="prazo ${s}">${rotulo}${formatarData(t.prazo)}</span>`;
}

// Pendentes: atrasadas primeiro, depois as de hoje, depois por prazo
function tarefasPendentes(){
    const peso = t => {
        const s = statusPrazo(t);
        if(s === "atrasada") return 0;
        if(s === "hoje") return 1;
        if(t.prazo) return 2;
        return 3;
    };

    return tarefas
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
// aoSalvar(valores) pode devolver false para manter a janela aberta
function abrirEditor(titulo, campos, aoSalvar){
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
    cancelar.textContent = "Cancelar";

    const salvarBtn = document.createElement("button");
    salvarBtn.className = "btn";
    salvarBtn.textContent = "Salvar";

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
        if(aoSalvar(valores) !== false) fechar();
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

function editarTarefa(id){
    const t = tarefas.find(x => x.id === id);
    if(!t) return;

    abrirEditor("Editar tarefa", [
        {chave:"texto", label:"Tarefa", tipo:"textarea", valor:t.texto},
        {chave:"prioridade", label:"Prioridade", tipo:"select",
         opcoes:["Normal","Importante","Urgente"], valor:t.prioridade},
        {chave:"prazo", label:"Prazo (opcional)", tipo:"date", valor:t.prazo || ""}
    ], v => {
        if(!v.texto){ alert("Digite a tarefa."); return false; }
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

    abrirEditor("Editar compromisso", [
        {chave:"nome", label:"Compromisso", valor:e.nome},
        {chave:"data", label:"Data", tipo:"date", valor:e.data},
        {chave:"hora", label:"Horário", tipo:"time", valor:e.hora || ""},
        {chave:"local", label:"Local", valor:e.local || ""}
    ], v => {
        if(!v.nome || !v.data){ alert("Informe o compromisso e a data."); return false; }
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

    abrirEditor("Editar anotação", [
        {chave:"titulo", label:"Título", valor:n.titulo},
        {chave:"texto", label:"Texto", tipo:"textarea", valor:n.texto}
    ], v => {
        if(!v.titulo || !v.texto){ alert("Preencha título e texto."); return false; }
        n.titulo = v.titulo;
        n.texto = v.texto;
        salvarTudo();
        renderizar();
    });
}

function editarGasto(id){
    const g = gastos.find(x => x.id === id);
    if(!g) return;

    abrirEditor("Editar gasto", [
        {chave:"descricao", label:"Descrição", valor:g.descricao},
        {chave:"valor", label:"Valor", tipo:"number", valor:g.valor},
        {chave:"categoria", label:"Categoria", tipo:"select",
         opcoes:["Alimentação","Transporte","Casa","Estudos","Lazer","Compras","Outros"],
         valor:g.categoria}
    ], v => {
        const novoValor = Number(v.valor);
        if(!v.descricao || !novoValor || novoValor <= 0){
            alert("Informe descrição e valor.");
            return false;
        }
        g.descricao = v.descricao;
        g.valor = novoValor;
        g.categoria = v.categoria;
        salvarTudo();
        renderizar();
    });
}

function editarCompra(id){
    const c = compras.find(x => x.id === id);
    if(!c) return;

    abrirEditor("Editar item", [
        {chave:"produto", label:"Produto", valor:c.produto}
    ], v => {
        if(!v.produto){ alert("Digite um produto."); return false; }
        c.produto = v.produto;
        salvarTudo();
        renderizar();
    });
}

/* ================= GRÁFICO DE GASTOS ================= */
function renderGrafico(){
    const div = $("graficoGastos");
    if(!div) return;

    if(!gastos.length){
        div.innerHTML = `<p class="vazio">Adicione gastos para ver o gráfico.</p>`;
        return;
    }

    const porCategoria = {};
    gastos.forEach(g => {
        porCategoria[g.categoria] =
            (porCategoria[g.categoria] || 0) + Number(g.valor);
    });

    const total = Object.values(porCategoria).reduce((a,b) => a + b, 0);

    div.innerHTML = Object.entries(porCategoria)
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

    // Atualiza os interruptores ON/OFF (menu e página Conta)
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
        ["Tarefas", tarefas],
        ["Agenda", agenda],
        ["Notas", notas],
        ["Gastos", gastos],
        ["Compras", compras],
        ["Estudos", estudos],
        ["Trabalhos", trabalhos]
    ];

    const linhas = [];

    linhas.push(celulaCSV("Renda") + ";" + celulaCSV(renda));
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
async function trocarSenha(){
    const atual = $("senhaAtual").value;
    const nova = $("senhaNova").value;
    const confirmar = $("senhaConfirmar").value;

    if(!atual || !nova || !confirmar){
        alert("Preencha todos os campos.");
        return;
    }

    if(nova.length < 6){
        alert("A nova senha precisa ter pelo menos 6 caracteres.");
        return;
    }

    if(nova !== confirmar){
        alert("A confirmação não coincide com a nova senha.");
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
            alert("Senha alterada com sucesso!");
            limpar("senhaAtual","senhaNova","senhaConfirmar");
        } else {
            alert(resposta.mensagem || "Não foi possível alterar a senha.");
        }
    } catch(e){
        alert("Erro de conexão. Tente novamente.");
    }
}

/* ================= INICIALIZAÇÃO ================= */

aplicarTema();
carregarPerfil();
mostrarData();
atualizarSaudacao();
renderizar();
