// script.js

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');
    const header = document.querySelector('.header');
    const contentItems = document.querySelectorAll('.content-item');
    const searchForm = document.querySelector('.search-form');

    if (header) {
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'sidebar-toggle';
        toggleBtn.textContent = '☰';
        toggleBtn.setAttribute('aria-label', 'Abrir menu lateral');
        header.appendChild(toggleBtn);

        if (sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });

            // Suporte a teclado para o botão de toggle
            toggleBtn.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    sidebar.classList.toggle('active');
                    e.preventDefault();
                }
            });

            // Fecha a sidebar ao clicar fora
            document.addEventListener('click', (e) => {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            });

            // Fecha a sidebar ao clicar em links
            sidebar.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                });
            });
        }
    }

    // Efeito de hover e toque nos itens do catálogo
    contentItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            item.style.transform = 'scale(1.05)';
            item.style.transition = 'transform 0.3s ease';
        });
        item.addEventListener('mouseleave', () => {
            item.style.transform = 'scale(1)';
        });
        item.addEventListener('touchstart', () => {
            item.style.transform = 'scale(1.05)';
            item.style.transition = 'transform 0.3s ease';
        });
        item.addEventListener('touchend', () => {
            item.style.transform = 'scale(1)';
        });
    });

    // Suporte ao formulário de busca
    if (searchForm) {
        searchForm.addEventListener('submit', () => {
            const input = searchForm.querySelector('input[name="query"]');
            input.value = input.value.trim();
            if (sidebar) {
                sidebar.classList.remove('active');
            }
        });
    }
});
