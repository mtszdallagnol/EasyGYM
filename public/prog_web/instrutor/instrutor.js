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
        document.querySelector('#modal-titulo').textContent = 'Adicionar Instrutor';
        editando = false;
    });

    // Fechar modal
    fecharModalBtn.addEventListener('click', () => {
        modalContainer.style.display = 'none';
    });


    // Função para carregar a tabela (usando os dados fixos)
    async function carregarTabela() {
        // Em um cenário real, substituiríamos por uma chamada fetch para o backend
        const request = await fetch("/php/controllers/FuncionarioController.php?id_academia=17", {
            method: "GET"
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
                <td>${item.id_funcionario}</td>
                <td>${item.nome_funcionario}</td>
                <td>${item.email_funcionario}</td>
                <td>
                    <button class="btn btn-edit" onclick="editarAtividade(${item.id_funcionario}, '${item.nome_funcionario}', '${item.email_funcionario}', '', '${item.senha_funcionario}')">Editar</button>
                    <button class="btn btn-delete" onclick="excluirAtividade(${item.id_funcionario})">Excluir</button>
                </td>
            `;
            tabela.appendChild(tr);
        });
    }

    function validatePasswords() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (password !== confirmPassword) {
            alert('As senhas não coincidem. Por favor, verifique novamente.');
            return false; // Prevent form submission
        }
        alert('Senhas coincidem! Formulário pode ser enviado.');
        return true; // Allow form submission
    }

    // Função para editar atividade
    // Tem que montar a integração com o back
    window.editarAtividade = (id, nome, email, cpf, password) => {
        document.querySelector('#modal-titulo').textContent = 'Editar Atividade';
        document.querySelector('#id-atividade').value = id;
        
        const elementName = document.querySelector('#nome');
        elementName.value = nome
        elementName.disabled = true

        const elementEmail = document.querySelector('#email');
        elementEmail.value = email;

        document.querySelector('#password').value = password;
        modalContainer.style.display = 'block';
        editando = true;
    };

    // Função para excluir atividade
    // Tem que montar a integração com o back
    window.excluirAtividade = async (id) => {
        if (confirm('Tem certeza que deseja excluir esta atividade?')) {
            // Aqui, simularíamos a exclusão do backend
            const request = await fetch("/php/controllers/FuncionarioController.php", {
                method: "DELETE",
                body: JSON.stringify({
                    id_funcionario: id,
                    id_academia: 17
                })
            })
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
        const email = document.querySelector('#email').value;
        const password = document.querySelector('#password').value;

            if (editando) {
                // Atualizar item existente
                await fetch("/php/controllers/FuncionarioController.php", {
                    method: "PUT",
                    body: JSON.stringify({
                        id_funcionario: id,
                        nome_funcionario: nome,
                        email_funcionario: email,
                        senha_funcionario: password,
                        id_academia: 17
                    })
                }).then(response => response.json()).then(response => {
                    console.log(response);
                    if (response[0] === "error") {
                        return;
                    }

                    const formFuncionario = document.getElementById("form-atividade");
                    Array.from(formFuncionario.elements).forEach(input => {
                        if (input.disabled) {
                            input.disabled = false;
                        }
                    })

                    carregarTabela();
                    modalContainer.style.display = 'none'; // Fechar a modal
                })
            } else {
                // Adicionar novo item
                await fetch("/php/controllers/FuncionarioController.php", {
                    method: "POST",
                    body: JSON.stringify({
                        nome_funcionario: nome,
                        email_funcionario: email,
                        senha_funcionario: password,
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
