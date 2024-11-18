document.addEventListener('DOMContentLoaded', () => {
    const tabelaTreinos = document.querySelector('#tabela-treinos tbody');
    const modalContainer = document.querySelector('#modal-container');
    const formTreino = document.querySelector('#form-treino');
    const fecharModalBtn = document.querySelector('#fechar-modal');
    let editando = false;

    // Dados fixos para demonstração (simulando dados de um backend)
    let treinosFixos = [
    ];

    // Abrir modal para adicionar treino
    document.querySelector('#adicionar-treino').addEventListener('click', () => {
        modalContainer.style.display = 'block';
        formTreino.reset();
        document.querySelector('#modal-titulo').textContent = 'Adicionar Treino';
        editando = false;
    });

    // Fechar modal
    fecharModalBtn.addEventListener('click', () => {
        modalContainer.style.display = 'none';
    });

    // Função para carregar a tabela (usando os dados fixos)
    async function carregarTabelaTreinos() {
        const request = await fetch("/php/controllers/TreinoController.php?id_academia=17", {
            method: "GET"
        })
        const response = await request.json();
        console.log(response);
        if (response[0] === "error") {
            return;
        }

        treinosFixos = response[1];
        const treinos = treinosFixos;

        tabelaTreinos.innerHTML = ''; // Limpar a tabela antes de adicionar novos dados
        treinos.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.id_treino}</td>
                <td>${item.nome_treino}</td>
                <td>${item.duracao_treino}</td>
                <td>${item.repeticoes_treino}</td>
                <td>
                    <button class="btn btn-edit" onclick="editarTreino(${item.id_treino}, '${item.nome_treino}', '${item.duracao_treino}', '${item.repeticoes_treino}')">Editar</button>
                    <button class="btn btn-delete" onclick="excluirTreino(${item.id_treino})">Excluir</button>
                </td>
            `;
            tabelaTreinos.appendChild(tr);
        });
    }

    // Função para editar treino
    window.editarTreino = (id, nome, duracao, repeticoes) => {
        document.querySelector('#modal-titulo').textContent = 'Editar Treino';
        document.querySelector('#id-treino').value = id;
        document.querySelector('#nome-exercicio').value = nome;
        document.querySelector('#duracao').value = duracao;
        document.querySelector('#repeticoes').value = repeticoes;
        modalContainer.style.display = 'block';
        editando = true;
    };

    // Função para excluir treino
    window.excluirTreino = async (id) => {
        if (confirm('Tem certeza que deseja excluir este treino?')) {
            const request = await fetch("/php/controllers/TreinoController.php", {
                method: "DELETE",
                body: JSON.stringify({
                    id_treino: id,
                    id_academia: 17
                })
            })
            const response = await request.json();
            console.log(response);
            if (response[0] === "error") {
                return;
            }

            carregarTabelaTreinos();
        }
    };

    // Enviar formulário (adicionar ou editar)
    formTreino.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = document.querySelector('#id-treino').value;
        const nome = document.querySelector('#nome-exercicio').value;
        const duracao = document.querySelector('#duracao').value;
        const repeticoes = document.querySelector('#repeticoes').value;

        if (editando) {
            await fetch("/php/controllers/TreinoController.php", {
                method: "PUT",
                body: JSON.stringify({
                    id_treino: id,
                    nome_treino: nome,
                    duracao_treino: duracao,
                    repeticoes_treino: repeticoes,
                    id_academia: 17
                })
            }).then(response => response.json()).then(response => {
                console.log(response);
                if (response[0] === "error") {
                    return;
                }

                carregarTabelaTreinos();
                modalContainer.style.display = 'none';
            })
        } else {
            await fetch("/php/controllers/TreinoController.php", {
                method: "POST",
                body: JSON.stringify({
                    nome_treino: nome,
                    duracao_treino: duracao,
                    repeticoes_treino: repeticoes,
                    id_academia: 17
                })
            }).then(response => response.json()).then(response => {
                console.log(response);
                if (response[0] === "error") {
                    return;
                }

                carregarTabelaTreinos();
                modalContainer.style.display = 'none';
            })
        }
    });

    // Carregar a tabela na inicialização
    carregarTabelaTreinos();
});
