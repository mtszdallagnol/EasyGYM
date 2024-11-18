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

    function validateCPF() {
        const cpf = document.getElementById('cpf').value.replace(/\D/g, ''); // Remove non-numeric characters

        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
            alert('CPF inválido. Por favor, insira um CPF válido.');
            return false; // Prevent form submission
        }

        let sum = 0;
        let remainder;

        // Validate first digit
        for (let i = 1; i <= 9; i++) {
            sum += parseInt(cpf.charAt(i - 1)) * (11 - i);
        }
        remainder = (sum * 10) % 11;

        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(cpf.charAt(9))) {
            alert('CPF inválido. Por favor, insira um CPF válido.');
            return false;
        }

        // Validate second digit
        sum = 0;
        for (let i = 1; i <= 10; i++) {
            sum += parseInt(cpf.charAt(i - 1)) * (12 - i);
        }
        remainder = (sum * 10) % 11;

        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(cpf.charAt(10))) {
            alert('CPF inválido. Por favor, insira um CPF válido.');
            return false;
        }

        alert('CPF válido!');
        return true;
    }

    // Função para carregar a tabela (usando os dados fixos)
    async function carregarTabela() {
        // Em um cenário real, substituiríamos por uma chamada fetch para o backend
        const request = await fetch("/php/controllers/AlunoController.php?id_academia=17", {
        method: "GET"
        })
        const data = await request.json();
        console.log(data);
        if (data[0] === "error") {
            return;
        }

        atividadesFixas = data[1];

        const atividades = atividadesFixas; 

        tabela.innerHTML = ''; // Limpar a tabela antes de adicionar novos dados
        //Preenche a tabela
        atividades.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.id_aluno}</td>
                <td>${item.nome_aluno}</td>
                <td>${item.email_aluno}</td>
                <td>${item.cpf_aluno}</td>
                <td>
                    <button class="btn btn-edit" onclick="editarAtividade(${item.id_aluno}, '${item.nome_aluno}', '${item.email_aluno}', '${item.cpf_aluno}', '${item.senha_aluno}')">Editar</button>
                    <button class="btn btn-delete" onclick="excluirAtividade(${item.id_aluno})">Excluir</button>
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
        const elementId = document.querySelector('#id-atividade')
        elementId.value = id
        elementId.disabled = true

        const elementName = document.querySelector('#nome')
        elementName.value = nome
        elementName.disabled = true

        const elementEmail = document.querySelector('#email')
        elementEmail.value = email

        const elementCPF = document.querySelector('#cpf')
        elementCPF.value = cpf
        elementCPF.disabled = true

        const elementPass = document.querySelector('#password')
        elementPass.value = password

        modalContainer.style.display = 'block';
        editando = true;
    };

    // Função para excluir atividade
    // Tem que montar a integração com o back
    window.excluirAtividade = async (id) => {
        if (confirm('Tem certeza que deseja excluir esta atividade?')) {
            // Aqui, simularíamos a exclusão do backend
            const request = await fetch("/php/controllers/AlunoController.php", {
                method: "DELETE",
                body: JSON.stringify({
                    id_academia: 17,
                    id_aluno: id
                })
            });
            const response = await request.json();
            console.log(response);
            if (response[0] === "error" || response[1] === 0) {
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
        const cpf = document.querySelector('#cpf').value;
        const password = document.querySelector('#password').value;
        if (validateCPF()) {
            if (editando) {
                // Atualizar item existente
                await fetch("/php/controllers/AlunoController.php", {
                    method: "PUT",
                    body: JSON.stringify({
                       id_aluno: id,
                       nome_aluno: nome,
                       email_aluno: email,
                       cpf_aluno: cpf,
                       senha_aluno: password,
                       id_academia: 17
                    })
                }).then(response => response.json()).then(response => {
                    console.log(response);
                    if (response[0] === "error") {
                        return;
                    }

                    carregarTabela();
                    
                    const DOMform = document.getElementById("form-atividade");
                    Array.from(DOMform.elements).forEach(element => {
                        element.disabled = false;
                    }) 

                    modalContainer.style.display = 'none';
                })
            } else {
                // Adicionar novo item
                await fetch("/php/controllers/AlunoController.php", {
                    method: "POST",
                    body: JSON.stringify({
                        nome_aluno: nome,
                        email_aluno: email,
                        cpf_aluno: cpf,
                        senha_aluno: password,
                        id_academia: 17
                    })
                }).then(response => response.json()).then(response => {
                    console.log(response);
                    if (response[0] === "error") {
                        return;
                    }
                    carregarTabela();
                    modalContainer.style.display = 'none';
                })
            }
        }
    });

    // Carregar a tabela na inicialização
    carregarTabela();
});
