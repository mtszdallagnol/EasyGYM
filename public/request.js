document.addEventListener('DOMContentLoaded', () => {
    const tabela = document.querySelector('#tabela-atividades tbody');
    const modalContainer = document.querySelector('#modal-container');
    const formAtividade = document.querySelector('#form-atividade');
    const fecharModalBtn = document.querySelector('#fechar-modal');
    let editando = false;

    // Abrir modal para adicionar atividade
    document.querySelector('#adicionar-atividade').addEventListener('click', () => {
        modalContainer.style.display = 'block';
        formAtividade.reset();
        document.querySelector('#modal-titulo').textContent = 'Adicionar Instrutor';
        editando = false;
    });

    // Fechar modal
    fecharModalBtn.addEventListener('click', () => {
        modalContainer.style.display = 'none';
    });

    // Função para carregar a tabela (com chamada fetch para o backend)
    async function carregarTabela() {
        try {
            const response = await fetch('backend_endpoint.php'); // Replace with your PHP endpoint URL
            const atividades = await response.json();

            tabela.innerHTML = ''; // Limpar a tabela antes de adicionar novos dados
            atividades.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.id}</td>
                    <td>${item.name}</td>
                    <td>${item.email}</td>
                    <td>
                        <button class="btn btn-edit" onclick="editarAtividade(${item.id}, '${item.name}', '${item.email}', '${item.password}')">Editar</button>
                        <button class="btn btn-delete" onclick="excluirAtividade(${item.id})">Excluir</button>
                    </td>
                `;
                tabela.appendChild(tr);
            });
        } catch (error) {
            console.error('Erro ao carregar a tabela:', error);
        }
    }

    // Função para enviar dados (adicionar ou editar)
    formAtividade.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = document.querySelector('#id-atividade').value;
        const nome = document.querySelector('#nome').value;
        const email = document.querySelector('#email').value;
        const password = document.querySelector('#password').value;
        const formData = { id, name: nome, email, password };

        try {
            if (editando) {
                // Fazer uma requisição PUT para atualizar um item
                await fetch(`backend_endpoint.php?id=${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
            } else {
                // Fazer uma requisição POST para adicionar um novo item
                await fetch('backend_endpoint.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
            }

            carregarTabela(); // Recarregar a tabela
            modalContainer.style.display = 'none'; // Fechar a modal
        } catch (error) {
            console.error('Erro ao enviar dados:', error);
        }
    });

    // Função para editar atividade (abrir modal)
    window.editarAtividade = (id, nome, email, password) => {
        document.querySelector('#modal-titulo').textContent = 'Editar Atividade';
        document.querySelector('#id-atividade').value = id;
        document.querySelector('#nome').value = nome;
        document.querySelector('#email').value = email;
        document.querySelector('#password').value = password;
        modalContainer.style.display = 'block';
        editando = true;
    };

    // Função para excluir atividade
    window.excluirAtividade = async (id) => {
        if (confirm('Tem certeza que deseja excluir esta atividade?')) {
            try {
                await fetch(`backend_endpoint.php?id=${id}`, {
                    method: 'DELETE'
                });
                carregarTabela(); // Recarregar a tabela após exclusão
            } catch (error) {
                console.error('Erro ao excluir a atividade:', error);
            }
        }
    };

    // Carregar a tabela na inicialização
    carregarTabela();
});
