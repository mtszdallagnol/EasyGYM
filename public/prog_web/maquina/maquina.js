document.addEventListener('DOMContentLoaded', () => {
    const tabela = document.querySelector('#tabela-atividades tbody');
    const modalContainer = document.querySelector('#modal-container');
    const formAtividade = document.querySelector('#form-atividade');
    const fecharModalBtn = document.querySelector('#fechar-modal');
    const submitAtividadeBtn = document.querySelector('#submit-atividade');
    let editando = false;

    // Dados fixos para demonstração (simulando dados de um backend)
    let atividadesFixas = [

    ];

    // Abrir modal para adicionar atividade
    document.querySelector('#adicionar-atividade').addEventListener('click', () => {
        modalContainer.style.display = 'block';
        formAtividade.reset();
        document.querySelector('#modal-titulo').textContent = 'Adicionar Atividade';
        editando = false;
    });

    // Fechar modal
    fecharModalBtn.addEventListener('click', () => {
        modalContainer.style.display = 'none';
    });

    // Função para carregar a tabela (usando os dados fixos)
    async function carregarTabela() {
        // Em um cenário real, substituiríamos por uma chamada fetch para o backend
        const request = await fetch("/php/controllers/EquipamentoController.php?id_academia=17", {
            method: "GET",
        })
        const response = await request.json();
        console.log(response);
        if (response[0] === "error") {
            return;
        }

        atividadesFixas = response[1];
        const atividades = atividadesFixas; // Usando os dados fixos

        tabela.innerHTML = ''; // Limpar a tabela antes de adicionar novos dados
        //Preenche a tabela
        atividades.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.id_equipamento}</td>
                <td>${item.nome_equipamento}</td>
                <td>${item.marca_equipamento}</td>
                <td>${item.modelo_equipamento}</td>
                <td>
                    <button class="btn btn-edit" onclick="editarAtividade(${item.id_equipamento}, '${item.nome_equipamento}', '${item.marca_equipamento}', '${item.modelo_equipamento}')">Editar</button>
                    <button class="btn btn-delete" onclick="excluirAtividade(${item.id_equipamento})">Excluir</button>
                </td>
            `;
            tabela.appendChild(tr);
        });
    }

    // Função para editar atividade
    // Tem que montar a integração com o back
    window.editarAtividade = (id, nome, marca, grupo) => {
        document.querySelector('#modal-titulo').textContent = 'Editar Máquina';
        document.querySelector('#id-atividade').value = id;
        document.querySelector('#nome').value = nome;
        document.querySelector('#marca').value = marca;
        document.querySelector('#grupo').value = grupo;
        modalContainer.style.display = 'block';
        editando = true;
    };

    // Função para excluir atividade
    // Tem que montar a integração com o back
    window.excluirAtividade = async (id) => {
        if (confirm('Tem certeza que deseja excluir esta atividade?')) {
            // Aqui, simularíamos a exclusão do backend
            const request = await fetch("/php/controllers/EquipamentoController.php", {
                method: "DELETE",
                body: JSON.stringify({
                    id_equipamento: id,
                    id_academia: 17
                })
            });
            const response = await request.json();
            console.log(response);
            if (response[0] === "error") {
                return;
            }

            carregarTabela();
        }
    };

    // Enviar formulário (adicionar ou editar)
    formAtividade.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = document.querySelector('#id-atividade').value;
        const nome = document.querySelector('#nome').value;
        const marca = document.querySelector('#marca').value;
        const grupo = document.querySelector('#grupo').value;

        if (editando) {
            // Atualizar item existente
            await fetch("/php/controllers/EquipamentoController.php", {
                method: "PUT",
                body: JSON.stringify({
                    id_equipamento: id,
                    nome_equipamento: nome,
                    marca_equipamento: marca,
                    modelo_equipamento: grupo,
                    id_academia: 17
                })
            }).then(response => response.json()).then(response => {
                console.log(response);
                if (response[0] === "error") {
                    return
                }

                carregarTabela();
                modalContainer.style.display = 'none'; // Fechar a modal
            })
        } else {
            // Adicionar novo item
            await fetch("/php/controllers/EquipamentoController.php", {
                method: "POST",
                body: JSON.stringify({
                    nome_equipamento: nome,
                    marca_equipamento: marca,
                    modelo_equipamento: grupo,
                    id_academia: 17
                })
            }).then(response => response.json()).then(response => {
                console.log(response);
                if (response[0] === "error") {
                    return;
                }

                carregarTabela();
                modalContainer.style.display = 'none'; // Fechar a modal
            })
        }
    });

    // Carregar a tabela na inicialização
    carregarTabela();
});
