// MeuDia+ | Login e cadastro: mostrar senha, força da senha, confirmação e botão carregando
function togglePwd(btn) {
  var input = btn.closest('.input-wrap').querySelector('input');
  var mostrar = input.type === 'password';
  input.type = mostrar ? 'text' : 'password';
  btn.classList.toggle('mostrando', mostrar);
  btn.setAttribute('aria-label', mostrar ? 'Esconder senha' : 'Mostrar senha');
}

(function () {
  var senha = document.getElementById('senha');
  var conf = document.getElementById('confirmar_senha');
  var forca = document.getElementById('forca');
  var dica = document.getElementById('dica-conf');

  if (senha && forca) {
    var nomes = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    senha.addEventListener('input', function () {
      var v = senha.value, n = 0;
      if (v.length >= 6) n++;
      if (v.length >= 10) n++;
      if (/[a-z]/.test(v) && /[A-Z]/.test(v) && /\d/.test(v)) n++;
      if (/[^A-Za-z0-9]/.test(v)) n++;
      if (!v) n = 0;
      forca.dataset.n = n;
      forca.querySelector('span').textContent = n ? 'Password ' + nomes[n].toLowerCase() : '';
      checar();
    });
  }
  function checar() {
    if (!conf || !dica) return;
    if (!conf.value) { dica.textContent = ''; dica.className = 'dica'; return; }
    var ok = conf.value === senha.value;
    dica.textContent = ok ? 'Passwords match' : 'Passwords do not match yet';
    dica.className = 'dica ' + (ok ? 'ok' : 'erro');
  }
  if (conf) conf.addEventListener('input', checar);

  var form = document.querySelector('.auth-form');
  if (form) form.addEventListener('submit', function () {
    var b = form.querySelector('.auth-btn');
    b.classList.add('carregando');
    setTimeout(function () { b.disabled = true; }, 0);
  });
  window.addEventListener('pageshow', function (e) {
    var b = document.querySelector('.auth-btn');
    if (e.persisted && b) { b.disabled = false; b.classList.remove('carregando'); }
  });
})();
