document.addEventListener('DOMContentLoaded', () => {
    const tabelaCargos = document.querySelector('#tabela-cargos tbody');
    const modalContainer = document.querySelector('#modal-container');
    const formCargo = document.querySelector('#form-cargo');
    const fecharModalBtn = document.querySelector('#fechar-modal');
    let editando = false;

    // Dados fixos para demonstração (simulando dados de um backend)
    let cargosFixos = [

    ];

    // Abrir modal para adicionar cargo
    document.querySelector('#adicionar-cargo').addEventListener('click', () => {
        modalContainer.style.display = 'block';
        formCargo.reset();
        document.querySelector('#modal-titulo').textContent = 'Adicionar Cargo';
        editando = false;
    });

    // Fechar modal
    fecharModalBtn.addEventListener('click', () => {
        modalContainer.style.display = 'none';
    });

    // Função para carregar a tabela (usando os dados fixos)
    async function carregarTabelaCargos() {
        const request = await fetch("/php/controllers/CargoController.php?id_academia=17",  {
            method: "GET"
        })
        const response = await request.json();
        console.log(response);
        if (response[0] === "error") {
            return;
        }

        cargosFixos = response[1];
        const cargos = cargosFixos;

        tabelaCargos.innerHTML = ''; // Limpar a tabela antes de adicionar novos dados
        cargos.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.id_cargo}</td>
                <td>${item.nome_cargo}</td>
                <td>${item.desc_cargo}</td>
                <td>
                    <button class="btn btn-edit" onclick="editarCargo(${item.id_cargo}, '${item.nome_cargo}', '${item.desc_cargo}')">Editar</button>
                    <button class="btn btn-delete" onclick="excluirCargo(${item.id_cargo})">Excluir</button>
                </td>
            `;
            tabelaCargos.appendChild(tr);
        });
    }

    // Função para editar cargo
    window.editarCargo = (id, nomeCargo, descricao) => {
        document.querySelector('#modal-titulo').textContent = 'Editar Cargo';

        const elementID = document.querySelector('#id-cargo');
        elementID.value = id;
        elementID.disabled = true;

        document.querySelector('#nome-cargo').value = nomeCargo;
        document.querySelector('#descricao').value = descricao;

        modalContainer.style.display = 'block';
        editando = true;
    };

    // Função para excluir cargo
    window.excluirCargo = async (id) => {
        console.log(id);
        if (confirm('Tem certeza que deseja excluir este cargo?')) {
            const request = await fetch("/php/controllers/CargoController.php", {
                method: "DELETE",
                body: JSON.stringify({
                    id_cargo: id,
                    id_academia: 17
                })
            })
            const response = await request.json();
            console.log(response);
            if (response[0] === "error") {
                return;
            }

            carregarTabelaCargos();
        }
    };

    // Enviar formulário (adicionar ou editar)
    formCargo.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = document.querySelector('#id-cargo').value;
        const nomeCargo = document.querySelector('#nome-cargo').value;
        const descricao = document.querySelector('#descricao').value;

        if (editando) {
            await fetch("/php/controllers/CargoController.php", {
                method: "PUT",
                body: JSON.stringify({
                    id_cargo: id,
                    nome_cargo: nomeCargo,
                    desc_cargo: descricao,
                    id_academia: 17
                })
            }).then(response => response.json()).then(response => {
                console.log(response);
                if (response[0] === "error") {
                    return;
                }

                const formCargo = document.getElementById("form-cargo");
                Array.from(formCargo.elements).forEach(input => {
                    if (input.disabled) {
                        input.disabled = false;
                    }
                })

                carregarTabelaCargos();
                modalContainer.style.display = 'none';
            })
        } else {
           await fetch("/php/controllers/CargoController.php",  {
                method: "POST",
                body: JSON.stringify({
                    nome_cargo: nomeCargo,
                    desc_cargo: descricao,
                    id_academia: 17
                })
           }).then(response => response.json()).then(response => {
                console.log(response)
                if (response[0] === "error") {
                    return;
                }

                carregarTabelaCargos();
                modalContainer.style.display = 'none';
           })
        }


    });

    // Carregar a tabela na inicialização
    carregarTabelaCargos();
});
