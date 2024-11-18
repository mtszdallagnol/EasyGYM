document.addEventListener('DOMContentLoaded', () => {
    const tabelaAulas = document.querySelector('#tabela-aulas tbody');
    const modalContainer = document.querySelector('#modal-container');
    const formAula = document.querySelector('#form-aula');
    const fecharModalBtn = document.querySelector('#fechar-modal');
    let editando = false;

    // Dados fixos para demonstração (simulando dados de um backend)
    let aulasFixas = [
    ];

    // Abrir modal para adicionar aula
    document.querySelector('#adicionar-aula-especial').addEventListener('click', () => {
        modalContainer.style.display = 'block';
        formAula.reset();
        document.querySelector('#modal-titulo').textContent = 'Adicionar Aula Especial';
        editando = false;
    });

    // Fechar modal
    fecharModalBtn.addEventListener('click', () => {
        modalContainer.style.display = 'none';
    });

    // Função para carregar a tabela (usando os dados fixos)
    async function carregarTabelaAulas() {
        const request = await fetch("/php/controllers/AulaController.php?id_academia=17", {
            method: "GET",
        })
        const response = await request.json();
        console.log(response);
        if (response[0] === "error") {
            return;
        }

        aulasFixas = response[1];
        const aulas = aulasFixas;

        tabelaAulas.innerHTML = ''; // Limpar a tabela antes de adicionar novos dados
        aulas.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.id_aula}</td>
                <td>${item.nome_atividade_aula}</td>
                <td>${item.nome_instrutor_aula}</td>
                <td>${item.duracao_aula}</td>
                <td>${item.dia_aula}</td>
                <td>
                    <button class="btn btn-edit" onclick="editarAula(${item.id_aula}, '${item.nome_atividade_aula}', '${item.nome_instrutor_aula}', '${item.duracao_aula}', '${item.dia_aula}')">Editar</button>
                    <button class="btn btn-delete" onclick="excluirAula(${item.id_aula})">Excluir</button>
                </td>
            `;
            tabelaAulas.appendChild(tr);
        });
    }

    // Função para editar aula
    window.editarAula = (id, nomeAtividade, instrutor, duracao, diaSemana) => {
        document.querySelector('#modal-titulo').textContent = 'Editar Aula Especial';
        document.querySelector('#id-aula').value = id;
        document.querySelector('#nome-atividade').value = nomeAtividade;
        document.querySelector('#instrutor').value = instrutor;
        document.querySelector('#duracao').value = duracao;
        document.querySelector('#dia-semana').value = diaSemana;
        modalContainer.style.display = 'block';
        editando = true;
    };

    // Função para excluir aula
    window.excluirAula = async (id) => {
        if (confirm('Tem certeza que deseja excluir esta aula especial?')) {
            const request = await fetch("/php/controllers/AulaController.php", {
                method: "DELETE",
                body: JSON.stringify({
                    id_academia: 17,
                    id_aula: id
                })
            })
            const response = await request.json();
            console.log(response);
            if (response[0] === "error") {
                return;
            }

            carregarTabelaAulas();
        }
    };

    // Enviar formulário (adicionar ou editar)
    formAula.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = document.querySelector('#id-aula').value;
        const nomeAtividade = document.querySelector('#nome-atividade').value;
        const instrutor = document.querySelector('#instrutor').value;
        const duracao = document.querySelector('#duracao').value;
        const diaSemana = document.querySelector('#dia-semana').value;

        if (editando) {
            await fetch("/php/controllers/AulaController.php", {
                method: "PUT",
                body: JSON.stringify({
                    id_aula: id,
                    nome_atividade_aula: nomeAtividade,
                    nome_instrutor_aula: instrutor,
                    duracao_aula: duracao,
                    dia_aula: diaSemana,
                    id_academia: 17
                })
            }).then(response => response.json()).then(response =>  {
                console.log(response);
                if (response[0] === "errro") {
                    return;
                }

                carregarTabelaAulas();
                modalContainer.style.display = 'none';
            })
        } else {
            await fetch("/php/controllers/AulaController.php", {
                method: "POST",
                body: JSON.stringify({
                    nome_atividade_aula: nomeAtividade,
                    nome_instrutor_aula: instrutor,
                    duracao_aula: duracao,
                    dia_aula: diaSemana,
                    id_academia: 17
                })
            }).then(response => response.json()).then(response => {
                console.log(response);
                if (response[0] === "error") {
                    return;
                }

                carregarTabelaAulas();
                modalContainer.style.display = 'none';

            })
        }
    });

    // Carregar a tabela na inicialização
    carregarTabelaAulas();
});
