<?php

try {
    $pdo = new PDO("mysql:host=localhost;dbname=loja_recuperacao;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

function cadastrarProduto($pdo) {
    $sql = $pdo->prepare("
        INSERT INTO produtos (nome, categoria, preco, quantidade, descricao)
        VALUES (?, ?, ?, ?, ?)
    ");
    $sql->execute([
        $_POST['nome'], $_POST['categoria'], $_POST['preco'],
        $_POST['quantidade'], $_POST['descricao']
    ]);
    alert("Produto cadastrado com sucesso!");
}

function alterarProduto($pdo) {
    $sql = $pdo->prepare("
        UPDATE produtos
        SET nome=?, categoria=?, preco=?, quantidade=?, descricao=?
        WHERE id_produto=?
    ");
    $sql->execute([
        $_POST['nome'], $_POST['categoria'], $_POST['preco'],
        $_POST['quantidade'], $_POST['descricao'], $_POST['id_produto']
    ]);
    alert("Produto alterado com sucesso!");
}

function excluirProduto($pdo) {
    $sql = $pdo->prepare("DELETE FROM produtos WHERE id_produto=?");
    $sql->execute([$_POST['id_produto']]);
    alert("Produto excluído com sucesso!");
}

function alert($msg) {
    echo "<script>alert('$msg');</script>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    switch ($acao) {
        case 'cadastrar': cadastrarProduto($pdo); break;
        case 'alterar':   alterarProduto($pdo); break;
        case 'excluir':   excluirProduto($pdo); break;
    }
}

$listaProdutos = $pdo->query("SELECT * FROM produtos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$todosProdutos = $pdo->query("SELECT * FROM produtos")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Gerenciamento de Produtos</title>

<style>
body { font-family: Arial; margin: 20px; }
.tabs { display: flex; border-bottom: 2px solid #ccc; margin-bottom: 20px; }
.tab {
    padding: 10px 20px; cursor: pointer; border: 1px solid #ccc;
    border-bottom: none; margin-right: 5px; background: #f2f2f2;
}
.active { background: #fff; font-weight: bold; }
.content { display: none; }
.visible { display: block; }
button { padding: 8px 15px; }
input, textarea { width: 250px; }
</style>

<script>
function abrirAba(aba) {
    document.querySelectorAll('.content').forEach(div => div.classList.remove('visible'));
    document.getElementById(aba).classList.add('visible');

    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.getElementById("btn_" + aba).classList.add('active');
}

function carregarDados(select, prefixo) {
    if (select.selectedIndex === 0) return;

    let opt = select.options[select.selectedIndex];

    const campos = ["id", "nome", "categoria", "preco", "quantidade", "descricao"];
    campos.forEach(c =>
        document.getElementById(`${prefixo}_${c}`).value = opt.dataset[c]
    );
}
</script>

</head>
<body>

<h1>Gerenciamento de Produtos</h1>

<div class="tabs">
    <div class="tab active" id="btn_cadastrar" onclick="abrirAba('cadastrar')">Cadastrar</div>
    <div class="tab" id="btn_alterar"   onclick="abrirAba('alterar')">Alterar</div>
    <div class="tab" id="btn_excluir"   onclick="abrirAba('excluir')">Excluir</div>
    <div class="tab" id="btn_consultar" onclick="abrirAba('consultar')">Consultar</div>
    <div class="tab" id="btn_listar"    onclick="abrirAba('listar')">Listar</div>
</div>

<div id="cadastrar" class="content visible">
    <h2>Cadastrar Produto</h2>

    <form method="POST">
        <input type="hidden" name="acao" value="cadastrar">

        <label>Nome:</label><br>
        <input type="text" name="nome" required><br><br>

        <label>Categoria:</label><br>
        <input type="text" name="categoria" required><br><br>

        <label>Preço:</label><br>
        <input type="number" step="0.01" name="preco" required><br><br>

        <label>Quantidade:</label><br>
        <input type="number" name="quantidade" required><br><br>

        <label>Descrição:</label><br>
        <textarea name="descricao" rows="4"></textarea><br><br>

        <button type="submit">Cadastrar</button>
    </form>
</div>

<div id="alterar" class="content">
    <h2>Alterar Produto</h2>

    <label>Selecione:</label><br>
    <select onchange="carregarDados(this,'alt')">
        <option>Selecione</option>
        <?php foreach ($listaProdutos as $p): ?>
            <option
                data-id="<?= $p['id_produto'] ?>"
                data-nome="<?= $p['nome'] ?>"
                data-categoria="<?= $p['categoria'] ?>"
                data-preco="<?= $p['preco'] ?>"
                data-quantidade="<?= $p['quantidade'] ?>"
                data-descricao="<?= $p['descricao'] ?>"
            ><?= $p['nome'] ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <form method="POST">
        <input type="hidden" name="acao" value="alterar">
        <input type="hidden" name="id_produto" id="alt_id">

        <label>Nome:</label><br>
        <input type="text" name="nome" id="alt_nome"><br><br>

        <label>Categoria:</label><br>
        <input type="text" name="categoria" id="alt_categoria"><br><br>

        <label>Preço:</label><br>
        <input type="number" step="0.01" name="preco" id="alt_preco"><br><br>

        <label>Quantidade:</label><br>
        <input type="number" name="quantidade" id="alt_quantidade"><br><br>

        <label>Descrição:</label><br>
        <textarea id="alt_descricao" name="descricao" rows="4"></textarea><br><br>

        <button type="submit">Salvar Alterações</button>
    </form>
</div>

<div id="excluir" class="content">
    <h2>Excluir Produto</h2>

    <label>Selecione:</label><br>
    <select onchange="carregarDados(this,'exc')">
        <option>Selecione</option>
        <?php foreach ($listaProdutos as $p): ?>
            <option
                data-id="<?= $p['id_produto'] ?>"
                data-nome="<?= $p['nome'] ?>"
                data-categoria="<?= $p['categoria'] ?>"
                data-preco="<?= $p['preco'] ?>"
                data-quantidade="<?= $p['quantidade'] ?>"
                data-descricao="<?= $p['descricao'] ?>"
            ><?= $p['nome'] ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <form method="POST" onsubmit="return confirm('Excluir produto?')">
        <input type="hidden" name="acao" value="excluir">
        <input type="hidden" name="id_produto" id="exc_id">

        <label>Nome:</label><br>
        <input type="text" id="exc_nome" readonly><br><br>

        <label>Categoria:</label><br>
        <input type="text" id="exc_categoria" readonly><br><br>

        <label>Preço:</label><br>
        <input type="number" id="exc_preco" readonly><br><br>

        <label>Quantidade:</label><br>
        <input type="number" id="exc_quantidade" readonly><br><br>

        <label>Descrição:</label><br>
        <textarea id="exc_descricao" rows="4" readonly></textarea><br><br>

        <button style="background:red;color:white;">Excluir</button>
    </form>
</div>

<div id="consultar" class="content">
    <h2>Consultar Produto</h2>

    <label>Selecione:</label><br>
    <select onchange="carregarDados(this,'cons')">
        <option>Selecione</option>
        <?php foreach ($listaProdutos as $p): ?>
            <option
                data-id="<?= $p['id_produto'] ?>"
                data-nome="<?= $p['nome'] ?>"
                data-categoria="<?= $p['categoria'] ?>"
                data-preco="<?= $p['preco'] ?>"
                data-quantidade="<?= $p['quantidade'] ?>"
                data-descricao="<?= $p['descricao'] ?>"
            ><?= $p['nome'] ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <input type="hidden" id="cons_id">

    <label>Nome:</label><br>
    <input type="text" id="cons_nome" readonly><br><br>

    <label>Categoria:</label><br>
    <input type="text" id="cons_categoria" readonly><br><br>

    <label>Preço:</label><br>
    <input type="number" id="cons_preco" readonly><br><br>

    <label>Quantidade:</label><br>
    <input type="number" id="cons_quantidade" readonly><br><br>

    <label>Descrição:</label><br>
    <textarea id="cons_descricao" rows="4" readonly></textarea><br><br>

</div>

<div id="listar" class="content">
    <h2>Lista de Produtos</h2>

    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th><th>Nome</th><th>Categoria</th>
            <th>Preço</th><th>Quantidade</th><th>Descrição</th>
        </tr>

        <?php foreach ($todosProdutos as $p): ?>
        <tr>
            <td><?= $p['id_produto'] ?></td>
            <td><?= $p['nome'] ?></td>
            <td><?= $p['categoria'] ?></td>
            <td><?= $p['preco'] ?></td>
            <td><?= $p['quantidade'] ?></td>
            <td><?= $p['descricao'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>